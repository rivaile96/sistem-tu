<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\PaymentChannel;
use App\Models\PosOrder;    // Model Hutang Kantin
use App\Models\Student;
use App\Models\StudentBill; // Model Tagihan Sekolah
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\PaymentOrder;
use App\Models\PaymentOrderItem;
use App\Services\Doku\DokuClient;
use Illuminate\Support\Str;

class ParentApiController extends Controller
{
    public function __construct(
        private ?DokuClient $dokuClient = null
    ) {
        $this->dokuClient = $dokuClient ?? app(DokuClient::class);
    }
    /**
     * Kembalikan seluruh profil siswa (anak) yang berhak diakses oleh principal saat ini.
     */
    private function resolveAuthorizedStudents(Request $request): Collection
    {
        $principal = $request->user();

        if (! $principal) {
            return collect();
        }

        if ($principal instanceof Student) {
            return collect([$principal]);
        }

        if ($principal instanceof User) {
            return $principal->authorizedStudents();
        }

        return Student::where('user_id', $principal->id)->orderBy('id')->get();
    }

    /**
     * Resolve Student (student_profiles) yang terautentikasi & terotorisasi.
     *
     * Anti-IDOR (SEC-07A):
     * - Jika parameter student_id atau nis dikirim (mis. wali murid dengan >1 anak,
     *   atau percobaan manipulasi IDOR), validasi bahwa siswa tersebut berada di dalam
     *   daftar anak yang berhak diakses oleh akun yang login.
     * - Jika tidak berhak, kembalikan JsonResponse 403 Forbidden secara eksplisit.
     * - Jika akun user atau profil siswa tidak aktif (dropped_out / graduated), tolak 403.
     */
    private function resolveAuthorizedStudentOrFail(Request $request): Student|JsonResponse
    {
        $principal = $request->user();

        if (! $principal) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (isset($principal->is_active) && $principal->is_active === false) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun tidak aktif.',
            ], 403);
        }

        $authorizedStudents = $this->resolveAuthorizedStudents($request);

        $requestedStudentId = $request->input('student_id') ?? $request->query('student_id');
        $requestedNis       = $request->input('nis') ?? $request->query('nis');

        if ($requestedStudentId !== null && $requestedStudentId !== '') {
            if (! is_numeric($requestedStudentId)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Format student_id tidak valid.',
                ], 422);
            }

            $targetId = (int) $requestedStudentId;
            $student  = $authorizedStudents->first(function (Student $s) use ($targetId) {
                return (int) $s->id === $targetId || (int) $s->user_id === $targetId;
            });

            if (! $student) {
                Log::warning('SEC-07A IDOR blocked on Sistem TU: unauthorized student_id requested', [
                    'principal_id'         => $principal->id,
                    'requested_student_id' => $targetId,
                    'ip'                   => $request->ip(),
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Anda tidak memiliki hak akses ke data siswa ini.',
                ], 403);
            }
        } elseif ($requestedNis !== null && trim((string) $requestedNis) !== '') {
            $targetNis = trim((string) $requestedNis);
            $student   = $authorizedStudents->first(function (Student $s) use ($targetNis) {
                return (string) $s->nis === $targetNis;
            });

            if (! $student) {
                Log::warning('SEC-07A IDOR blocked on Sistem TU: unauthorized nis requested', [
                    'principal_id'  => $principal->id,
                    'requested_nis' => $targetNis,
                    'ip'            => $request->ip(),
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Anda tidak memiliki hak akses ke data siswa ini.',
                ], 403);
            }
        } else {
            $student = $authorizedStudents->first();

            if (! $student) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: profil siswa tidak ditemukan.',
                ], 403);
            }
        }

        if (isset($student->status) && ! in_array($student->status, ['active'], true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun siswa tidak aktif.',
            ], 403);
        }

        return $student;
    }

    // =================================================================
    // 1. LOGIN (Legacy Ortu Login — diamankan dengan verifikasi password)
    // =================================================================
    public function login(Request $request)
    {
        $request->validate([
            'nis'      => 'required|string|max:50',
            'password' => 'required|string|max:255',
            'phone'    => 'nullable|string|max:30',
        ]);

        $nis     = trim((string) $request->nis);
        $student = Student::where('nis', $nis)->first();
        $user    = $student?->user ?? User::where('username', $nis)->whereIn('role', ['student', 'parent', 'ortu'])->first();

        if (! $student || ! $user || ! Hash::check((string) $request->password, $user->password)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kredensial login tidak valid.',
            ], 401);
        }

        if (($user->is_active ?? true) === false || ! in_array($student->status ?? 'active', ['active'], true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akun siswa tidak aktif.',
            ], 403);
        }

        $user->tokens()->where('name', 'ParentApp')->delete();
        $expiresAt = now()->addHours((int) config('finance_assertion.finance_token_expiry_hours', 24));
        $token     = $user->createToken('ParentApp', ['finance'], $expiresAt)->plainTextToken;

        return response()->json([
            'status'     => 'success',
            'message'    => 'Login Berhasil',
            'token'      => $token,
            'expires_at' => $expiresAt->toIso8601String(),
            'student'    => [
                'id'         => $student->id,
                'nis'        => $student->nis,
                'nisn'       => $student->nisn,
                'name'       => $student->name ?? $user->name,
                'class_name' => $student->class_name,
            ],
        ]);
    }

    // =================================================================
    // 2. DASHBOARD (List Semua Tagihan: SPP + Uang Gedung + Kantin)
    // =================================================================
    public function getHomeData(Request $request)
    {
        $user    = $request->user();
        $student = $this->resolveAuthorizedStudentOrFail($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        // A. AMBIL SPP (Status UNPAID) — gunakan student_profiles.id, BUKAN users.id
        $sppBills = StudentBill::where('student_id', $student->id)
            ->where('type', 'SPP')
            ->where('status', 'UNPAID')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($bill) {
                return [
                    'type'   => 'SPP',           // Penanda untuk Icon di Android
                    'id'     => $bill->id,
                    'title'  => $bill->name,     // Contoh: "SPP Februari 2026"
                    'desc'   => 'Wajib Bulan Ini', 
                    'amount' => $bill->amount,
                    'date'   => $bill->created_at->format('d M Y'),
                ];
            });

        // B. AMBIL TAGIHAN LAINNYA (Gedung, Seragam, dll) - Status UNPAID
        $otherBills = StudentBill::where('student_id', $student->id)
            ->where('type', '!=', 'SPP')
            ->where('status', 'UNPAID')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($bill) {
                return [
                    'type'   => 'BILL',          // Penanda Icon Tagihan Umum
                    'id'     => $bill->id,
                    'title'  => $bill->name,     // Contoh: "Uang Pangkal"
                    'desc'   => 'Tagihan Sekolah',
                    'amount' => $bill->amount,
                    'date'   => $bill->created_at->format('d M Y'),
                ];
            });

        // C. AMBIL HUTANG KANTIN (POS) - Status UNPAID
        // Pastikan model PosOrder sudah diload
        $canteenDebts = PosOrder::where('student_id', $student->id)
            ->where('payment_status', 'UNPAID')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($trx) {
                // Hitung jumlah item biar informatif (Contoh: "Jajan Kantin (3 Item)")
                $itemsCount = $trx->items ? $trx->items->count() : 0;
                
                return [
                    'type'   => 'POS',           // Penanda Icon Makanan
                    'id'     => $trx->id,
                    'title'  => 'Jajan Kantin',
                    'desc'   => $trx->created_at->format('H:i') . " WIB • ($itemsCount Item)",
                    'amount' => $trx->total_amount,
                    'date'   => $trx->created_at->format('d M Y'),
                ];
            });

        // D. GABUNG SEMUA JADI SATU LIST (Gunakan toBase() agar aman saat menggabungkan array hasil mapping)
        $allBills = $sppBills->toBase()->concat($otherBills->toBase())->concat($canteenDebts->toBase());

        // E. HITUNG TOTAL TAGIHAN
        $grandTotal = $allBills->sum('amount');

        $children = $this->resolveAuthorizedStudents($request)->map(function (Student $child) {
            return [
                'id'         => $child->id,
                'nis'        => $child->nis,
                'nisn'       => $child->nisn,
                'name'       => $child->name,
                'class_name' => $child->class_name,
            ];
        })->values();

        return response()->json([
            'status'       => 'success',
            'student_id'   => $student->id,
            'student_name' => $student->name ?? $user?->name,
            'nis'          => $student->nis,
            'class_name'   => $student->class_name,
            'children'     => $children,

            // Ringkasan Header Dashboard
            'summary' => [
                'total_tagihan'    => $grandTotal,
                'dashboard_amount' => $grandTotal,
                'count'            => $allBills->count()
            ],

            // List untuk RecyclerView Android
            'list_tagihan' => $allBills
        ]);
    }

    // =================================================================
    // 3. CREATE PAYMENT (DOKU Direct API & SNAP — Multi-bill & Single Bill)
    // =================================================================
    public function createPayment(Request $request)
    {
        $user    = $request->user();
        $student = $this->resolveAuthorizedStudentOrFail($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $customerName = $student->name ?? $user?->name ?? 'Siswa Kaffah';
        $customerPhone = $student->parent_phone ?? $user?->phone ?? '08123456789';
        $paymentType = strtolower((string) $request->input('payment_type', 'bank_transfer'));
        $bank = strtolower((string) $request->input('bank', 'bca'));
        $store = strtolower((string) $request->input('store', ''));

        // Normalize payment type for channel
        if (in_array($paymentType, ['gopay', 'shopeepay', 'ovo', 'dana'], true)) {
            // E-Wallet
            $channel = $paymentType;
        } elseif ($paymentType === 'qris') {
            $channel = 'qris';
        } elseif ($paymentType === 'cstore' || in_array($store, ['indomaret', 'alfamart'], true)) {
            $channel = $store ?: 'indomaret';
            $paymentType = 'cstore';
        } else {
            $paymentType = 'bank_transfer';
            $channel = $bank ?: 'bca';
        }

        // --- SKENARIO 1: MULTI-BILL (bill_ids array) ---
        if ($request->has('bill_ids')) {
            $request->validate([
                'bill_ids'     => 'required|array|min:1',
                'bill_ids.*'   => 'required|integer',
                'payment_type' => 'nullable|string|max:50',
                'bank'         => 'nullable|string|max:30',
                'store'        => 'nullable|string|max:30',
            ]);

            $billIds = array_values(array_unique(array_map('intval', $request->input('bill_ids', []))));

            // Anti-IDOR: pastikan tidak ada satu pun bill_id milik siswa lain
            $foreignBillExists = StudentBill::whereIn('id', $billIds)
                ->where('student_id', '!=', $student->id)
                ->exists();

            if ($foreignBillExists) {
                Log::warning('SEC-07A IDOR blocked on createPayment: foreign bill_ids', [
                    'student_id' => $student->id,
                    'bill_ids'   => $billIds,
                    'ip'         => $request->ip(),
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Terdapat tagihan yang bukan milik siswa ini.',
                ], 403);
            }

            $bills = StudentBill::where('student_id', $student->id)
                ->whereIn('id', $billIds)
                ->get();

            if ($bills->count() !== count($billIds) || $bills->contains(fn ($b) => $b->status === 'PAID')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tagihan tidak valid atau sudah lunas',
                ], 400);
            }

            // 1. Expire order lama yang sudah lewat batas waktu
            PaymentOrder::where('status', 'PENDING')
                ->where('expiry_time', '<=', now())
                ->update(['status' => 'EXPIRED']);

            // 2. Deteksi sesi pembayaran aktif (overlap & reuse)
            $activeConflictingOrders = PaymentOrder::where('status', 'PENDING')
                ->where('expiry_time', '>', now())
                ->whereHas('items', function ($q) use ($billIds) {
                    $q->whereIn('bill_id', $billIds);
                })
                ->with('items')
                ->get();

            if ($activeConflictingOrders->isNotEmpty()) {
                // Cari apakah ada order milik siswa ini yang bill_ids-nya cocok persis
                $matchingOrder = $activeConflictingOrders->first(function ($ord) use ($student, $billIds) {
                    if ((int) $ord->student_id !== (int) $student->id) {
                        return false;
                    }
                    $orderBillIds = $ord->items->pluck('bill_id')->filter()->map(fn($id) => (int)$id)->sort()->values()->all();
                    $reqBillIds = collect($billIds)->map(fn($id) => (int)$id)->sort()->values()->all();
                    return $orderBillIds === $reqBillIds;
                });

                if ($matchingOrder && $activeConflictingOrders->count() === 1) {
                    // REUSE active order (Idempoten, tidak panggil DOKU ulang)
                    return response()->json([
                        'status'          => 'success',
                        'reused'          => true,
                        'message'         => 'Sesi pembayaran aktif ditemukan. Melanjutkan transaksi yang ada.',
                        'order_id'        => $matchingOrder->order_id,
                        'amount'          => (int) $matchingOrder->amount,
                        'payment_type'    => $matchingOrder->payment_channel === 'qris' ? 'qris' : 'va',
                        'payment_method'  => $matchingOrder->payment_method,
                        'payment_channel' => $matchingOrder->payment_channel,
                        'va_number'       => $matchingOrder->va_number,
                        'payment_code'    => $matchingOrder->payment_code,
                        'qr_url'          => $matchingOrder->qr_url,
                        'qr_content'      => $matchingOrder->qr_content,
                        'payment_url'     => $matchingOrder->payment_url,
                        'expiry_time'     => $matchingOrder->expiry_time?->toIso8601String(),
                    ]);
                }

                // Jika overlap parsial atau beda kombinasi: tolak dengan 409 Conflict
                $conflict = $activeConflictingOrders->first();
                return response()->json([
                    'status'          => 'error',
                    'message'         => "Terdapat tagihan yang sedang dalam sesi pembayaran aktif (Order #{$conflict->order_id}). Selesaikan atau batalkan sesi pembayaran tersebut terlebih dahulu.",
                    'active_order_id' => $conflict->order_id,
                ], 409);
            }

            // Hitung gross_amount dari SUM database — TIDAK mempercayai nominal frontend
            $grossAmount = (float) $bills->sum('amount');
            $orderId = 'ORD-TU-B' . $bills->first()->id . '-' . date('Ymd') . '-' . Str::lower(Str::random(6));

            // Call DOKU Gateway
            try {
                if ($channel === 'qris') {
                    $dokuRes = $this->dokuClient->generateQris([
                        'order_id'      => $orderId,
                        'amount'        => $grossAmount,
                        'customer_name' => $customerName,
                    ]);
                } else {
                    $dokuRes = $this->dokuClient->createVirtualAccount([
                        'order_id'       => $orderId,
                        'amount'         => $grossAmount,
                        'customer_name'  => $customerName,
                        'customer_phone' => $customerPhone,
                        'bank'           => $channel,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('ParentApiController: DOKU gateway call failed', ['error' => $e->getMessage()]);
                return response()->json(['status' => 'error', 'message' => 'Gagal menghubungkan ke payment gateway DOKU.'], 500);
            }

            // Simpan PaymentOrder & Items secara atomik
            $order = DB::transaction(function () use ($student, $orderId, $grossAmount, $paymentType, $channel, $dokuRes, $bills) {
                $order = PaymentOrder::create([
                    'order_id'           => $orderId,
                    'student_id'         => $student->id,
                    'amount'             => $grossAmount,
                    'currency'           => 'IDR',
                    'provider'           => 'doku',
                    'payment_method'     => 'DOKU',
                    'payment_channel'    => $channel,
                    'va_number'          => $dokuRes['va_number'] ?? null,
                    'payment_code'       => $dokuRes['payment_code'] ?? null,
                    'qr_content'         => $dokuRes['qr_content'] ?? null,
                    'qr_url'             => $dokuRes['qr_url'] ?? null,
                    'payment_url'        => $dokuRes['payment_url'] ?? null,
                    'status'             => 'PENDING',
                    'provider_reference' => $dokuRes['reference_id'] ?? null,
                    'expiry_time'        => $dokuRes['expiry_time'] ?? now()->addDay(),
                    'raw_response'       => $dokuRes,
                ]);

                foreach ($bills as $bill) {
                    PaymentOrderItem::create([
                        'payment_order_id' => $order->id,
                        'bill_id'          => $bill->id,
                        'pos_order_id'     => null,
                        'amount'           => $bill->amount,
                        'name'             => $bill->name,
                    ]);

                    $bill->update([
                        'payment_method'    => 'DOKU',
                    ]);
                }

                return $order;
            });

            return response()->json([
                'status'       => 'success',
                'order_id'     => $order->order_id,
                'amount'       => (int) $order->amount,
                'payment_type' => $paymentType,
                'va_number'    => $dokuRes['va_number'] ?? null,
                'payment_code' => $dokuRes['payment_code'] ?? null,
                'qr_url'       => $dokuRes['qr_url'] ?? null,
                'qr_content'   => $dokuRes['qr_content'] ?? null,
                'payment_url'  => $dokuRes['payment_url'] ?? null,
                'expiry_time'  => $dokuRes['expiry_time'] ?? null,
            ]);
        }

        // --- SKENARIO 2: SINGLE BILL ATAU POS (Backward Compat) ---
        $request->validate([
            'id'   => 'required|integer',
            'type' => 'required|string|in:SPP,BILL,POS',
        ]);

        if ($request->type === 'BILL' || $request->type === 'SPP') {
            // Anti-IDOR check: cek apakah bill milik siswa lain
            $foreignBill = StudentBill::whereKey($request->id)
                ->where('student_id', '!=', $student->id)
                ->exists();

            if ($foreignBill) {
                Log::warning('SEC-07A IDOR blocked on createPayment: foreign single bill', [
                    'student_id' => $student->id,
                    'bill_id'    => $request->id,
                    'ip'         => $request->ip(),
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Tagihan bukan milik siswa ini.',
                ], 403);
            }

            $bill = StudentBill::whereKey($request->id)
                ->where('student_id', $student->id)
                ->first();

            if (!$bill || $bill->status === 'PAID') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tagihan tidak valid atau sudah lunas',
                ], 400);
            }

            // Deteksi sesi pembayaran aktif (overlap & reuse) untuk single bill
            $billId = (int) $bill->id;
            $activeOrder = PaymentOrder::where('status', 'PENDING')
                ->where('expiry_time', '>', now())
                ->whereHas('items', function ($q) use ($billId) {
                    $q->where('bill_id', $billId);
                })
                ->with('items')
                ->first();

            if ($activeOrder) {
                if ((int) $activeOrder->student_id === (int) $student->id && $activeOrder->items->count() === 1) {
                    return response()->json([
                        'status'          => 'success',
                        'reused'          => true,
                        'message'         => 'Sesi pembayaran aktif ditemukan. Melanjutkan transaksi yang ada.',
                        'order_id'        => $activeOrder->order_id,
                        'amount'          => (int) $activeOrder->amount,
                        'payment_type'    => $activeOrder->payment_channel === 'qris' ? 'qris' : 'va',
                        'payment_method'  => $activeOrder->payment_method,
                        'payment_channel' => $activeOrder->payment_channel,
                        'va_number'       => $activeOrder->va_number,
                        'payment_code'    => $activeOrder->payment_code,
                        'qr_url'          => $activeOrder->qr_url,
                        'qr_content'      => $activeOrder->qr_content,
                        'payment_url'     => $activeOrder->payment_url,
                        'expiry_time'     => $activeOrder->expiry_time?->toIso8601String(),
                    ]);
                }

                return response()->json([
                    'status'          => 'error',
                    'message'         => "Tagihan sedang dalam sesi pembayaran aktif (Order #{$activeOrder->order_id}). Selesaikan atau batalkan sesi pembayaran tersebut terlebih dahulu.",
                    'active_order_id' => $activeOrder->order_id,
                ], 409);
            }

            $grossAmount = (float) $bill->amount;
            $orderId = 'ORD-TU-B' . $bill->id . '-' . date('Ymd') . '-' . Str::lower(Str::random(6));

            try {
                if ($channel === 'qris') {
                    $dokuRes = $this->dokuClient->generateQris([
                        'order_id'      => $orderId,
                        'amount'        => $grossAmount,
                        'customer_name' => $customerName,
                    ]);
                } else {
                    $dokuRes = $this->dokuClient->createVirtualAccount([
                        'order_id'       => $orderId,
                        'amount'         => $grossAmount,
                        'customer_name'  => $customerName,
                        'customer_phone' => $customerPhone,
                        'bank'           => $channel,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('ParentApiController: DOKU gateway call failed', ['error' => $e->getMessage()]);
                return response()->json(['status' => 'error', 'message' => 'Gagal menghubungkan ke payment gateway DOKU.'], 500);
            }

            $order = DB::transaction(function () use ($student, $orderId, $grossAmount, $paymentType, $channel, $dokuRes, $bill) {
                $order = PaymentOrder::create([
                    'order_id'           => $orderId,
                    'student_id'         => $student->id,
                    'amount'             => $grossAmount,
                    'currency'           => 'IDR',
                    'provider'           => 'doku',
                    'payment_method'     => 'DOKU',
                    'payment_channel'    => $channel,
                    'va_number'          => $dokuRes['va_number'] ?? null,
                    'payment_code'       => $dokuRes['payment_code'] ?? null,
                    'qr_content'         => $dokuRes['qr_content'] ?? null,
                    'qr_url'             => $dokuRes['qr_url'] ?? null,
                    'payment_url'        => $dokuRes['payment_url'] ?? null,
                    'status'             => 'PENDING',
                    'provider_reference' => $dokuRes['reference_id'] ?? null,
                    'expiry_time'        => $dokuRes['expiry_time'] ?? now()->addDay(),
                    'raw_response'       => $dokuRes,
                ]);

                PaymentOrderItem::create([
                    'payment_order_id' => $order->id,
                    'bill_id'          => $bill->id,
                    'pos_order_id'     => null,
                    'amount'           => $bill->amount,
                    'name'             => $bill->name,
                ]);

                $bill->update([
                    'midtrans_order_id' => $orderId,
                    'payment_method'    => 'DOKU',
                ]);

                return $order;
            });

            return response()->json([
                'status'       => 'success',
                'order_id'     => $order->order_id,
                'amount'       => (int) $order->amount,
                'payment_type' => $paymentType,
                'va_number'    => $dokuRes['va_number'] ?? null,
                'payment_code' => $dokuRes['payment_code'] ?? null,
                'qr_url'       => $dokuRes['qr_url'] ?? null,
                'qr_content'   => $dokuRes['qr_content'] ?? null,
                'payment_url'  => $dokuRes['payment_url'] ?? null,
                'expiry_time'  => $dokuRes['expiry_time'] ?? null,
            ]);

        } elseif ($request->type === 'POS') {
            $foreignPos = PosOrder::whereKey($request->id)
                ->where('student_id', '!=', $student->id)
                ->exists();

            if ($foreignPos) {
                Log::warning('SEC-07A IDOR blocked on createPayment: foreign pos_id', [
                    'student_id' => $student->id,
                    'pos_id'     => $request->id,
                    'ip'         => $request->ip(),
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Transaksi bukan milik siswa ini.',
                ], 403);
            }

            $pos = PosOrder::whereKey($request->id)
                ->where('student_id', $student->id)
                ->first();

            if (!$pos || $pos->payment_status === 'PAID') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Transaksi tidak valid atau sudah lunas',
                ], 400);
            }

            // Deteksi sesi pembayaran aktif (overlap & reuse) untuk POS
            $activePosOrder = PaymentOrder::where('status', 'PENDING')
                ->where('expiry_time', '>', now())
                ->whereHas('items', function ($q) use ($pos) {
                    $q->where('pos_order_id', $pos->id);
                })
                ->first();

            if ($activePosOrder) {
                if ((int) $activePosOrder->student_id === (int) $student->id) {
                    return response()->json([
                        'status'          => 'success',
                        'reused'          => true,
                        'message'         => 'Sesi pembayaran aktif ditemukan. Melanjutkan transaksi yang ada.',
                        'order_id'        => $activePosOrder->order_id,
                        'amount'          => (int) $activePosOrder->amount,
                        'payment_type'    => $activePosOrder->payment_channel === 'qris' ? 'qris' : 'va',
                        'payment_method'  => $activePosOrder->payment_method,
                        'payment_channel' => $activePosOrder->payment_channel,
                        'va_number'       => $activePosOrder->va_number,
                        'payment_code'    => $activePosOrder->payment_code,
                        'qr_url'          => $activePosOrder->qr_url,
                        'qr_content'      => $activePosOrder->qr_content,
                        'payment_url'     => $activePosOrder->payment_url,
                        'expiry_time'     => $activePosOrder->expiry_time?->toIso8601String(),
                    ]);
                }

                return response()->json([
                    'status'          => 'error',
                    'message'         => "Transaksi kantin sedang dalam sesi pembayaran aktif (Order #{$activePosOrder->order_id}). Selesaikan atau batalkan sesi pembayaran tersebut terlebih dahulu.",
                    'active_order_id' => $activePosOrder->order_id,
                ], 409);
            }

            $grossAmount = (float) $pos->total_amount;
            $orderId = 'ORD-TU-POS' . $pos->id . '-' . date('Ymd') . '-' . Str::lower(Str::random(6));

            try {
                if ($channel === 'qris') {
                    $dokuRes = $this->dokuClient->generateQris([
                        'order_id'      => $orderId,
                        'amount'        => $grossAmount,
                        'customer_name' => $customerName,
                    ]);
                } else {
                    $dokuRes = $this->dokuClient->createVirtualAccount([
                        'order_id'       => $orderId,
                        'amount'         => $grossAmount,
                        'customer_name'  => $customerName,
                        'customer_phone' => $customerPhone,
                        'bank'           => $channel,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('ParentApiController: DOKU gateway call failed', ['error' => $e->getMessage()]);
                return response()->json(['status' => 'error', 'message' => 'Gagal menghubungkan ke payment gateway DOKU.'], 500);
            }

            $order = DB::transaction(function () use ($student, $orderId, $grossAmount, $paymentType, $channel, $dokuRes, $pos) {
                $order = PaymentOrder::create([
                    'order_id'           => $orderId,
                    'student_id'         => $student->id,
                    'amount'             => $grossAmount,
                    'currency'           => 'IDR',
                    'provider'           => 'doku',
                    'payment_method'     => 'DOKU',
                    'payment_channel'    => $channel,
                    'va_number'          => $dokuRes['va_number'] ?? null,
                    'payment_code'       => $dokuRes['payment_code'] ?? null,
                    'qr_content'         => $dokuRes['qr_content'] ?? null,
                    'qr_url'             => $dokuRes['qr_url'] ?? null,
                    'payment_url'        => $dokuRes['payment_url'] ?? null,
                    'status'             => 'PENDING',
                    'provider_reference' => $dokuRes['reference_id'] ?? null,
                    'expiry_time'        => $dokuRes['expiry_time'] ?? now()->addDay(),
                    'raw_response'       => $dokuRes,
                ]);

                PaymentOrderItem::create([
                    'payment_order_id' => $order->id,
                    'bill_id'          => null,
                    'pos_order_id'     => $pos->id,
                    'amount'           => $pos->total_amount,
                    'name'             => 'Jajan Kantin ' . $pos->created_at?->format('d/m'),
                ]);

                $pos->update([
                    'payment_status'   => 'PENDING',
                    'transaction_code' => $orderId,
                    'payment_method'   => 'DOKU',
                ]);

                return $order;
            });

            return response()->json([
                'status'       => 'success',
                'order_id'     => $order->order_id,
                'amount'       => (int) $order->amount,
                'payment_type' => $paymentType,
                'va_number'    => $dokuRes['va_number'] ?? null,
                'payment_code' => $dokuRes['payment_code'] ?? null,
                'qr_url'       => $dokuRes['qr_url'] ?? null,
                'qr_content'   => $dokuRes['qr_content'] ?? null,
                'payment_url'  => $dokuRes['payment_url'] ?? null,
                'expiry_time'  => $dokuRes['expiry_time'] ?? null,
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Tipe pembayaran tidak dikenal'], 400);
    }

    /**
     * GET /api/finance/bills
     * Kembalikan semua tagihan UNPAID milik student yang sedang login.
     */
    public function getBills(Request $request)
    {
        $student = $this->resolveAuthorizedStudentOrFail($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $bills = StudentBill::where('student_id', $student->id)
            ->where('status', 'UNPAID')
            ->latest()
            ->get()
            ->map(function ($bill) {
                return [
                    'id'       => $bill->id,
                    'type'     => $bill->type,
                    'name'     => $bill->name,
                    'amount'   => $bill->amount,
                    'status'   => $bill->status,
                    'due_date' => $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('Y-m-d') : null,
                    'date'     => $bill->created_at ? $bill->created_at->format('d M Y') : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $bills,
        ]);
    }

    /**
     * GET /api/finance/transactions
     * Riwayat pembayaran & transaksi yang di-scope ke student_profiles.id milik siswa login.
     */
    public function getTransactionHistory(Request $request)
    {
        $student = $this->resolveAuthorizedStudentOrFail($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $transactions = StudentBill::where('student_id', $student->id)
            ->whereIn('status', ['PAID', 'PENDING'])
            ->latest()
            ->get()
            ->map(function ($bill) {
                $dateSource = $bill->paid_at ?? $bill->updated_at ?? $bill->created_at;

                return [
                    'id'     => $bill->midtrans_order_id ?? ('BILL-' . $bill->id),
                    'title'  => $bill->name,
                    'type'   => strtolower((string) $bill->type),
                    'amount' => (int) $bill->amount,
                    'status' => $bill->status === 'PAID' ? 'success' : 'pending',
                    'method' => $bill->payment_method ?? 'DOKU',
                    'date'   => $dateSource?->format('d M Y'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $transactions,
        ]);
    }

    /**
     * GET /api/finance/payment/status/{order_id}
     * Cek status pembayaran berdasarkan order_id yang di-scope ke student_profiles.id milik siswa login.
     */
    public function checkStatus(Request $request, string $orderId)
    {
        $student = $this->resolveAuthorizedStudentOrFail($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        // 1. Cek PaymentOrder (DOKU & Multi-bill)
        $paymentOrder = PaymentOrder::where('student_id', $student->id)
            ->where('order_id', $orderId)
            ->with(['items.bill', 'items.posOrder'])
            ->first();

        if ($paymentOrder) {
            // Cek kedaluwarsa 24 jam
            if ($paymentOrder->status === 'PENDING' && $paymentOrder->expiry_time && $paymentOrder->expiry_time->isPast()) {
                $paymentOrder->update(['status' => 'EXPIRED']);
                foreach ($paymentOrder->items as $item) {
                    if ($item->bill && $item->bill->status !== 'PAID') {
                        $item->bill->update(['status' => 'UNPAID', 'midtrans_order_id' => null]);
                    }
                    if ($item->posOrder && $item->posOrder->payment_status !== 'PAID') {
                        $item->posOrder->update(['payment_status' => 'UNPAID', 'transaction_code' => null]);
                    }
                }
            }

            return response()->json([
                'status'         => 'success',
                'order_id'       => $orderId,
                'payment_status' => $paymentOrder->status,
                'amount'         => (int) $paymentOrder->amount,
                'expiry_time'    => $paymentOrder->expiry_time?->toIso8601String(),
            ]);
        }

        // 2. Cek StudentBill (legacy / fallback)
        $bill = StudentBill::where('student_id', $student->id)
            ->where('midtrans_order_id', $orderId)
            ->first();

        if ($bill) {
            return response()->json([
                'status'         => 'success',
                'order_id'       => $orderId,
                'payment_status' => $bill->status,
            ]);
        }

        // 3. Cek PosOrder (Kantin)
        $pos = PosOrder::where('student_id', $student->id)
            ->where('transaction_code', $orderId)
            ->first();

        if ($pos) {
            return response()->json([
                'status'         => 'success',
                'order_id'       => $orderId,
                'payment_status' => $pos->payment_status,
            ]);
        }

        // Anti-IDOR: cek apakah order_id milik siswa lain
        $foreignOrder = PaymentOrder::where('order_id', $orderId)
            ->where('student_id', '!=', $student->id)
            ->exists();
        $foreignBill = StudentBill::where('midtrans_order_id', $orderId)
            ->where('student_id', '!=', $student->id)
            ->exists();
        $foreignPos = PosOrder::where('transaction_code', $orderId)
            ->where('student_id', '!=', $student->id)
            ->exists();

        if ($foreignOrder || $foreignBill || $foreignPos) {
            Log::warning('SEC-07A IDOR blocked on checkStatus: foreign order_id', [
                'student_id' => $student->id,
                'order_id'   => $orderId,
                'ip'         => $request->ip(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak: Transaksi bukan milik siswa ini.',
            ], 403);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Transaksi tidak ditemukan.',
        ], 404);
    }

    /**
     * POST /api/finance/payment/cancel
     * Batalkan sesi pembayaran yang sedang aktif (PENDING).
     *
     * Alur State Machine:
     * 1. Validasi kepemilikan (Anti-IDOR) & status awal (wajib PENDING).
     * 2. Tandai CANCEL_REQUESTED dengan row lock atomik.
     * 3. Hubungi provider payment gateway (DOKU) untuk membatalkan tagihan.
     * 4. Evaluasi ulang state:
     *    - Bila webhook tiba duluan dan status sudah PAID -> PAID MENANG! (batalkan pembatalan).
     *    - Bila provider sukses membatalkan -> status CANCELLED, tagihan dibebaskan (UNPAID).
     *    - Bila provider gagal/timeout -> kembalikan ke PENDING.
     */
    public function cancelPayment(Request $request): JsonResponse
    {
        $student = $this->resolveAuthorizedStudentOrFail($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $request->validate([
            'order_id' => 'required|string',
        ]);

        $orderId = (string) $request->input('order_id');

        // Anti-IDOR: pastikan order milik siswa ini
        $foreignOrder = PaymentOrder::where('order_id', $orderId)
            ->where('student_id', '!=', $student->id)
            ->exists();

        if ($foreignOrder) {
            Log::warning('SEC-07A IDOR blocked on cancelPayment: foreign order_id', [
                'student_id' => $student->id,
                'order_id'   => $orderId,
                'ip'         => $request->ip(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak: Order bukan milik siswa ini.',
            ], 403);
        }

        $order = PaymentOrder::where('order_id', $orderId)
            ->where('student_id', $student->id)
            ->first();

        if (!$order) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Order pembayaran tidak ditemukan.',
            ], 404);
        }

        if ($order->status === 'PAID') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Order sudah lunas, tidak dapat dibatalkan.',
            ], 400);
        }

        if ($order->status === 'CANCELLED') {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Order sudah dibatalkan.',
                'order_id' => $order->order_id,
            ]);
        }

        if ($order->status !== 'PENDING') {
            return response()->json([
                'status'  => 'error',
                'message' => "Order dengan status {$order->status} tidak dapat dibatalkan.",
            ], 400);
        }

        // 1. Set CANCEL_REQUESTED secara atomik
        DB::transaction(function () use ($order) {
            $locked = PaymentOrder::where('id', $order->id)->lockForUpdate()->first();
            if ($locked && $locked->status === 'PENDING') {
                $locked->update(['status' => 'CANCEL_REQUESTED']);
            }
        });

        // 2. Panggil pembatalan ke provider DOKU
        $cancelSuccess = false;
        $cancelMessage = 'Gagal membatalkan transaksi pada payment gateway. Transaksi masih aktif dan dapat dibayar menggunakan kode/VA yang ada.';
        $cancellationStatus = 'FAILED';

        try {
            $result = $this->dokuClient->cancelOrder($order);
            $cancelSuccess = (bool) ($result['success'] ?? false);
            $cancellationStatus = $result['status'] ?? ($cancelSuccess ? 'CANCELLED' : 'REJECTED');
            if (!$cancelSuccess && !empty($result['raw']['message'])) {
                $cancelMessage = 'Pembatalan ditolak oleh payment gateway: ' . $result['raw']['message'];
            }
        } catch (\Throwable $e) {
            Log::error('ParentApiController: cancelOrder provider error', [
                'order_id' => $order->order_id,
                'error'    => $e->getMessage(),
            ]);
            $cancelSuccess = false;
            $cancellationStatus = 'UNCERTAIN';
        }

        // 3. Re-lock dan evaluasi hasil
        return DB::transaction(function () use ($order, $cancelSuccess, $cancelMessage, $cancellationStatus) {
            $locked = PaymentOrder::where('id', $order->id)
                ->with(['items.bill', 'items.posOrder'])
                ->lockForUpdate()
                ->first();

            // Jika webhook tiba saat pembatalan dan sudah PAID: PAID MENANG!
            if ($locked->status === 'PAID') {
                return response()->json([
                    'status'   => 'error',
                    'message'  => 'Pembayaran telah diterima oleh gateway. Pembatalan dibatalkan.',
                    'order_id' => $locked->order_id,
                ], 400);
            }

            if (!$cancelSuccess) {
                // Revert status ke PENDING (Tagihan tetap terkunci di sesi pembayaran aktif)
                $locked->update(['status' => 'PENDING']);
                return response()->json([
                    'status'              => 'error',
                    'message'             => $cancelMessage,
                    'cancellation_status' => $cancellationStatus,
                    'order_id'            => $locked->order_id,
                ], 400);
            }

            // Sukses batalkan: transisi ke CANCELLED & bebaskan tagihan
            $locked->update(['status' => 'CANCELLED']);

            foreach ($locked->items as $item) {
                if ($item->bill && $item->bill->status !== 'PAID') {
                    $item->bill->update([
                        'status'            => 'UNPAID',
                        'midtrans_order_id' => null,
                    ]);
                }
                if ($item->posOrder && $item->posOrder->payment_status !== 'PAID') {
                    $item->posOrder->update([
                        'payment_status'   => 'UNPAID',
                        'transaction_code' => null,
                    ]);
                }
            }

            Log::info('ParentApiController: order berhasil dibatalkan oleh pengguna', [
                'order_id'   => $locked->order_id,
                'student_id' => $locked->student_id,
            ]);

            return response()->json([
                'status'   => 'success',
                'message'  => 'Sesi pembayaran berhasil dibatalkan. Anda dapat memilih metode pembayaran baru.',
                'order_id' => $locked->order_id,
            ]);
        });
    }

    /**
     * GET /api/finance/payment/active
     * Ambil sesi pembayaran aktif (PENDING dan belum expired) milik siswa login jika ada.
     */
    public function getActivePayment(Request $request): JsonResponse
    {
        $student = $this->resolveAuthorizedStudentOrFail($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $activeOrder = PaymentOrder::where('student_id', $student->id)
            ->where('status', 'PENDING')
            ->where('expiry_time', '>', now())
            ->with('items')
            ->latest()
            ->first();

        if (!$activeOrder) {
            return response()->json([
                'status'     => 'success',
                'has_active' => false,
                'data'       => null,
            ]);
        }

        return response()->json([
            'status'     => 'success',
            'has_active' => true,
            'data'       => [
                'order_id'          => $activeOrder->order_id,
                'amount'            => (int) $activeOrder->amount,
                'payment_method'    => $activeOrder->payment_method,
                'payment_channel'   => $activeOrder->payment_channel,
                'va_number'         => $activeOrder->va_number,
                'payment_code'      => $activeOrder->payment_code,
                'qr_url'            => $activeOrder->qr_url,
                'qr_content'        => $activeOrder->qr_content,
                'payment_url'       => $activeOrder->payment_url,
                'expiry_time'       => $activeOrder->expiry_time?->toIso8601String(),
                'remaining_seconds' => max(0, now()->diffInSeconds($activeOrder->expiry_time, false)),
                'bill_ids'          => $activeOrder->items->pluck('bill_id')->filter()->values(),
            ],
        ]);
    }

    /**
     * GET /api/finance/payment/invoice/{order_id}
     * Detail invoice pembayaran yang di-scope ke student_profiles.id milik siswa login.
     */
    public function getInvoiceDetail(Request $request, string $orderId)
    {
        $user    = $request->user();
        $student = $this->resolveAuthorizedStudentOrFail($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        // 1. Cek PaymentOrder (DOKU & Multi-bill)
        $paymentOrder = PaymentOrder::with('items')
            ->where('student_id', $student->id)
            ->where('order_id', $orderId)
            ->first();

        if ($paymentOrder) {
            $paidAtSource = $paymentOrder->paid_at ?? $paymentOrder->updated_at ?? $paymentOrder->created_at;
            $items = $paymentOrder->items->map(function ($item) {
                return [
                    'name'   => $item->name,
                    'amount' => (int) $item->amount,
                ];
            })->values()->all();

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'order_id'       => $paymentOrder->order_id,
                    'student_name'   => $student->name ?? $user?->name,
                    'nis'            => $student->nis,
                    'class_name'     => $student->class_name,
                    'paid_at'        => $paidAtSource?->format('d M Y, H:i'),
                    'payment_method' => $paymentOrder->payment_method ?? 'DOKU',
                    'status'         => $paymentOrder->status,
                    'total_amount'   => (int) $paymentOrder->amount,
                    'items'          => $items,
                ],
            ]);
        }

        // 2. Cek StudentBill (legacy / fallback)
        $bill = StudentBill::where('student_id', $student->id)
            ->where('midtrans_order_id', $orderId)
            ->first();

        if (!$bill && ctype_digit($orderId)) {
            $bill = StudentBill::where('student_id', $student->id)
                ->whereKey((int) $orderId)
                ->first();
        }

        if (!$bill) {
            // Anti-IDOR: cek apakah invoice milik siswa lain
            $foreignOrder = PaymentOrder::where('order_id', $orderId)
                ->where('student_id', '!=', $student->id)
                ->exists();

            $foreignBill = StudentBill::where('midtrans_order_id', $orderId)
                ->where('student_id', '!=', $student->id)
                ->exists();

            if (! $foreignBill && ctype_digit($orderId)) {
                $foreignBill = StudentBill::whereKey((int) $orderId)
                    ->where('student_id', '!=', $student->id)
                    ->exists();
            }

            if ($foreignOrder || $foreignBill) {
                Log::warning('SEC-07A IDOR blocked on getInvoiceDetail: foreign order_id/bill_id', [
                    'student_id' => $student->id,
                    'order_id'   => $orderId,
                    'ip'         => $request->ip(),
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Invoice bukan milik siswa ini.',
                ], 403);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Invoice tidak ditemukan.',
            ], 404);
        }

        $paidAtSource = $bill->paid_at ?? $bill->updated_at ?? $bill->created_at;

        return response()->json([
            'status' => 'success',
            'data'   => [
                'order_id'       => $bill->midtrans_order_id ?? (string) $bill->id,
                'student_name'   => $student->name ?? $user?->name,
                'nis'            => $student->nis,
                'class_name'     => $student->class_name,
                'paid_at'        => $paidAtSource?->format('d M Y, H:i'),
                'payment_method' => $bill->payment_method ?? 'DOKU',
                'status'         => $bill->status,
                'total_amount'   => (int) $bill->amount,
                'items'          => [
                    [
                        'name'   => $bill->name,
                        'amount' => (int) $bill->amount,
                    ],
                ],
            ],
        ]);
    }

    /**
     * POST /api/finance/pin/set
     * Atur / perbarui 6-digit PIN keamanan keuangan (disimpan sebagai one-way bcrypt hash).
     * PIN tidak pernah disimpan plaintext, tidak pernah dicatat ke log, dan tidak pernah dikembalikan di response.
     */
    public function setPin(Request $request)
    {
        $principal = $request->user();
        $student   = $this->resolveAuthorizedStudentOrFail($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $request->validate([
            'pin'         => ['required', 'string', 'regex:/^[0-9]{6}$/'],
            'current_pin' => ['nullable', 'string', 'regex:/^[0-9]{6}$/'],
        ], [
            'pin.required' => 'PIN wajib diisi.',
            'pin.regex'    => 'PIN harus terdiri dari 6 digit angka.',
        ]);

        $user        = $principal instanceof User ? $principal : $student->user;
        $existingPin = $user?->pin ?? $student->pin;

        if ($existingPin && $request->filled('current_pin')) {
            if (! Hash::check((string) $request->input('current_pin'), $existingPin)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'PIN lama yang Anda masukkan salah.',
                ], 401);
            }
        }

        $hashedPin = Hash::make((string) $request->input('pin'));

        if ($user && Schema::hasColumn('users', 'pin')) {
            $user->pin = $hashedPin;
            $user->save();
        }

        if (Schema::hasColumn('student_profiles', 'pin')) {
            $student->pin = $hashedPin;
            $student->save();
        }

        Log::info('SEC-07A: Finance PIN updated', [
            'user_id'    => $user?->id,
            'student_id' => $student->id,
            'ip'         => $request->ip(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'PIN keamanan berhasil disimpan.',
        ]);
    }

    /**
     * POST /api/finance/pin/verify
     * Verifikasi 6-digit PIN keamanan keuangan sebelum transaksi pembayaran.
     * Dilindungi rate limiter throttle:5,1 di route.
     */
    public function verifyPin(Request $request)
    {
        $principal = $request->user();
        $student   = $this->resolveAuthorizedStudentOrFail($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $request->validate([
            'pin' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
        ], [
            'pin.required' => 'PIN wajib diisi.',
            'pin.regex'    => 'PIN harus terdiri dari 6 digit angka.',
        ]);

        $user      = $principal instanceof User ? $principal : $student->user;
        $hashedPin = $user?->pin ?? $student->pin;

        if (empty($hashedPin)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'PIN keamanan belum diatur. Silakan atur PIN terlebih dahulu.',
            ], 422);
        }

        if (! Hash::check((string) $request->input('pin'), $hashedPin)) {
            Log::warning('SEC-07A: Finance PIN verification failed', [
                'user_id'    => $user?->id,
                'student_id' => $student->id,
                'ip'         => $request->ip(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'PIN yang Anda masukkan salah.',
            ], 401);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'PIN valid.',
        ]);
    }

    /**
     * GET /api/finance/banners
     */
    public function getBanners(Request $request)
    {
        $student = $this->resolveAuthorizedStudentOrFail($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $banners = Schema::hasTable('banners')
            ? Banner::where('is_active', true)->latest()->get(['id', 'title', 'image_url', 'link_to'])
            : collect();

        return response()->json([
            'status' => 'success',
            'data'   => $banners,
        ]);
    }

    /**
     * GET /api/finance/payment-channels
     */
    public function getPaymentChannels(Request $request)
    {
        $student = $this->resolveAuthorizedStudentOrFail($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        $channels = Schema::hasTable('payment_channels')
            ? PaymentChannel::where('is_active', true)->get()
            : collect();

        $formatChannel = fn ($ch) => [
            'id'   => $ch->code,
            'code' => $ch->code,
            'name' => $ch->name,
            'logo' => $ch->logo,
        ];

        return response()->json([
            'status' => 'success',
            'data'   => [
                'virtual_account' => $channels->where('type', 'virtual_account')->map($formatChannel)->values(),
                'ewallet'         => $channels->where('type', 'ewallet')->map($formatChannel)->values(),
                'cstore'          => $channels->where('type', 'cstore')->map($formatChannel)->values(),
                'qris'            => $channels->where('type', 'qris')->map($formatChannel)->values(),
            ],
        ]);
    }
}