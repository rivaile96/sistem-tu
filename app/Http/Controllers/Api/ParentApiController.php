<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentBill; // Model Tagihan Sekolah
use App\Models\PosOrder;    // Model Hutang Kantin
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\DB;

class ParentApiController extends Controller
{
    /**
     * Resolve the authenticated Student profile (student_profiles) from the Sanctum principal.
     * Supports tokens issued on User (via user_id -> student_profiles.user_id) or directly on Student.
     */
    private function resolveAuthenticatedStudent(Request $request): ?Student
    {
        $principal = $request->user();

        if (! $principal) {
            return null;
        }

        if ($principal instanceof Student) {
            return $principal;
        }

        return Student::where('user_id', $principal->id)->first();
    }

    // =================================================================
    // 1. LOGIN (Ortu Login pakai NIS & No HP)
    // =================================================================
    public function login(Request $request)
    {
        $request->validate([
            'nis' => 'required',
            'phone' => 'required',
        ]);

        // Cek data siswa berdasarkan NIS dan No HP Orang Tua
        $student = Student::where('nis', $request->nis)
                          ->where('parent_phone', $request->parent_phone ?? $request->phone) // Jaga-jaga nama param beda
                          ->first();

        if (!$student) {
            return response()->json(['message' => 'NIS atau No HP salah!'], 401);
        }

        // Buat Token Akses (Sanctum)
        $token = $student->createToken('ParentApp')->plainTextToken;

        return response()->json([
            'message' => 'Login Berhasil',
            'token' => $token,
            'student' => $student
        ]);
    }

    // =================================================================
    // 2. DASHBOARD (List Semua Tagihan: SPP + Uang Gedung + Kantin)
    // =================================================================
    public function getHomeData(Request $request)
    {
        $user    = $request->user();
        $student = $this->resolveAuthenticatedStudent($request);

        if (! $student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak: profil siswa tidak ditemukan.',
            ], 403);
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

        return response()->json([
            'status'       => 'success',
            'student_name' => $student->name ?? $user?->name,
            'nis'          => $student->nis,
            'class_name'   => $student->class_name,
            
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
    // 3. CREATE PAYMENT (Support SPP & Kantin)
    // =================================================================
    public function createPayment(Request $request)
    {
        $user    = $request->user();
        $student = $this->resolveAuthenticatedStudent($request);

        if (! $student) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak: profil siswa tidak ditemukan.',
            ], 403);
        }

        // Request dari Android wajib bawa: id & type (SPP/BILL/POS)
        $request->validate([
            'id' => 'required',
            'type' => 'required' 
        ]);

        $transactionDetails = [];
        $customerDetails = [];
        $orderIdPrefix = '';
        $amount = 0;
        $itemName = '';

        // --- SKENARIO A: BAYAR TAGIHAN SEKOLAH (SPP/LAINNYA) ---
        if ($request->type == 'BILL' || $request->type == 'SPP') {
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
        $student = $this->resolveAuthenticatedStudent($request);

        // Guard: user tanpa student profile tidak boleh akses finance bills
        if (!$student) {
            return response()->json(['message' => 'Akses ditolak: profil siswa tidak ditemukan.'], 403);
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
        $student = $this->resolveAuthenticatedStudent($request);

        if (!$student) {
            return response()->json(['message' => 'Akses ditolak: profil siswa tidak ditemukan.'], 403);
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
        $student = $this->resolveAuthenticatedStudent($request);

        if (!$student) {
            return response()->json(['message' => 'Akses ditolak: profil siswa tidak ditemukan.'], 403);
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
        $student = $this->resolveAuthenticatedStudent($request);

        if (!$student) {
            return response()->json(['message' => 'Akses ditolak: profil siswa tidak ditemukan.'], 403);
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
}