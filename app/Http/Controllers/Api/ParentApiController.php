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
use Midtrans\Config;
use Midtrans\Snap;

class ParentApiController extends Controller
{
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

        // D. GABUNG SEMUA JADI SATU LIST
        $allBills = $sppBills->merge($otherBills)->merge($canteenDebts);

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
    // 3. CREATE PAYMENT (Support SPP, Tagihan Sekolah & Kantin)
    // =================================================================
    public function createPayment(Request $request)
    {
        $user    = $request->user();
        $student = $this->resolveAuthorizedStudentOrFail($request);

        if ($student instanceof JsonResponse) {
            return $student;
        }

        // --- SKENARIO MOBILE MULTI-BILL (bill_ids array) ---
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

            $firstBill = $bills->first();
            $request->merge([
                'id'   => $firstBill->id,
                'type' => $firstBill->type === 'SPP' ? 'SPP' : 'BILL',
            ]);
        }

        // Request wajib bawa: id & type (SPP/BILL/POS)
        $request->validate([
            'id'   => 'required|integer',
            'type' => 'required|string|in:SPP,BILL,POS',
        ]);

        $transactionDetails = [];
        $customerDetails = [];
        $orderIdPrefix = '';
        $amount = 0;
        $itemName = '';

        // --- SKENARIO A: BAYAR TAGIHAN SEKOLAH (SPP/LAINNYA) ---
        if ($request->type == 'BILL' || $request->type == 'SPP') {
            $foreignBill = StudentBill::whereKey($request->id)
                ->where('student_id', '!=', $student->id)
                ->exists();

            if ($foreignBill) {
                Log::warning('SEC-07A IDOR blocked on createPayment: foreign bill id', [
                    'student_id' => $student->id,
                    'bill_id'    => $request->id,
                    'ip'         => $request->ip(),
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Tagihan bukan milik siswa ini.',
                ], 403);
            }

            $bill = StudentBill::with('student')
                ->where('student_id', $student->id)
                ->whereKey($request->id)
                ->first();
            
            if (!$bill || $bill->status == 'PAID') {
                return response()->json(['message' => 'Tagihan tidak valid atau sudah lunas'], 400);
            }
            
            $amount = $bill->amount;
            $orderIdPrefix = 'BILL'; // Kode Unik Bill
            $itemName = substr($bill->name, 0, 50);
            
            $customerDetails = [
                'first_name' => $student->name ?? $user?->name,
                'phone' => $student->parent_phone ?? $user?->phone,
            ];

        // --- SKENARIO B: BAYAR HUTANG KANTIN (POS) ---
        } elseif ($request->type == 'POS') {
            $foreignPos = PosOrder::whereKey($request->id)
                ->where('student_id', '!=', $student->id)
                ->exists();

            if ($foreignPos) {
                Log::warning('SEC-07A IDOR blocked on createPayment: foreign pos id', [
                    'student_id' => $student->id,
                    'pos_id'     => $request->id,
                    'ip'         => $request->ip(),
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Akses ditolak: Transaksi bukan milik siswa ini.',
                ], 403);
            }

            $pos = PosOrder::with('student')
                ->where('student_id', $student->id)
                ->whereKey($request->id)
                ->first();
            
            if (!$pos || $pos->payment_status == 'PAID') {
                return response()->json(['message' => 'Transaksi tidak valid atau sudah lunas'], 400);
            }

            $amount = $pos->total_amount;
            $orderIdPrefix = 'POS'; // Kode Unik POS
            $itemName = "Jajan Kantin " . $pos->created_at->format('d/m');

            $customerDetails = [
                'first_name' => $student->name ?? $user?->name,
                'phone' => $student->parent_phone ?? $user?->phone,
            ];

        } else {
            return response()->json(['message' => 'Tipe pembayaran tidak dikenal'], 400);
        }

        // --- KONFIGURASI MIDTRANS ---
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Bikin Order ID Unik: TIPE-ID-TIMESTAMP 
        // Contoh: POS-15-17628392 (Biar ID 15 di SPP dan ID 15 di POS gak bentrok)
        $orderId = $orderIdPrefix . '-' . $request->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount,
            ],
            'customer_details' => $customerDetails,
            'item_details' => [[
                'id' => $request->id,
                'price' => (int) $amount,
                'quantity' => 1,
                'name' => $itemName
            ]]
        ];

        try {
            // Minta Token ke Midtrans
            $snapToken = Snap::getSnapToken($params);

            // Simpan order_id ke bill — webhook handler mencari bill via midtrans_order_id.
            // amount diambil server-side dari bill, tidak dipercaya dari frontend.
            if (isset($bill)) {
                $bill->update([
                    'midtrans_order_id' => $orderId,
                    'payment_token'     => $snapToken,
                    'status'            => 'PENDING',
                ]);
            }

            return response()->json([
                'snap_token' => $snapToken,
                'order_id'   => $orderId,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // =================================================================
    // 4. WEBHOOK (Callback Midtrans - Support Multi Table)
    // =================================================================
    public function callback(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        
        // 1. Validasi Signature Key (Wajib demi keamanan)
        $hashed = hash("sha512", $request->order_id.$request->status_code.$request->gross_amount.$serverKey);
        
        if ($hashed == $request->signature_key) {
            
            // 2. Cek Status Transaksi (Settlement / Capture = Sukses)
            if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                
                // Pecah Order ID: "POS-15-17628392"
                $parts = explode('-', $request->order_id);
                $type = $parts[0]; // "POS" atau "BILL"
                $id = $parts[1];   // "15"

                try {
                    DB::beginTransaction();

                    // A. UPDATE TABEL TAGIHAN SEKOLAH
                    if ($type == 'BILL') {
                        $bill = StudentBill::find($id);
                        if ($bill) {
                            $bill->update([
                                'status' => 'PAID',
                                'payment_method' => 'MIDTRANS',
                                'payment_token' => null, // Hapus token
                                'updated_at' => now()
                            ]);
                        }

                    // B. UPDATE TABEL POS (KANTIN)
                    } elseif ($type == 'POS') {
                        $pos = PosOrder::find($id);
                        if ($pos) {
                            $pos->update([
                                'payment_status' => 'PAID',
                                'payment_method' => 'MIDTRANS', 
                                'paid_amount' => $pos->total_amount, // Anggap lunas
                                'change_amount' => 0,
                                'updated_at' => now()
                            ]);
                        }
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    // Log error jika perlu
                }
            }
        }

        // Selalu return OK 200 ke Midtrans
        return response()->json(['status' => 'ok']);
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
                    'id'     => $bill->id,
                    'type'   => $bill->type,
                    'name'   => $bill->name,
                    'amount' => $bill->amount,
                    'status' => $bill->status,
                    'date'   => $bill->created_at->format('d M Y'),
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
                    'method' => $bill->payment_method ?? 'MIDTRANS',
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
        $foreignBill = StudentBill::where('midtrans_order_id', $orderId)
            ->where('student_id', '!=', $student->id)
            ->exists();
        $foreignPos = PosOrder::where('transaction_code', $orderId)
            ->where('student_id', '!=', $student->id)
            ->exists();

        if ($foreignBill || $foreignPos) {
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
            $foreignBill = StudentBill::where('midtrans_order_id', $orderId)
                ->where('student_id', '!=', $student->id)
                ->exists();

            if (! $foreignBill && ctype_digit($orderId)) {
                $foreignBill = StudentBill::whereKey((int) $orderId)
                    ->where('student_id', '!=', $student->id)
                    ->exists();
            }

            if ($foreignBill) {
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
                'payment_method' => $bill->payment_method ?? 'MIDTRANS',
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