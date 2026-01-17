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
        $student = $request->user(); // Ambil user dari token

        // A. AMBIL SPP (Status UNPAID)
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
            'student_name' => $student->name,
            'nis'          => $student->nis,
            'class_name'   => $student->class_name,
            
            // Ringkasan Header Dashboard
            'summary' => [
                'total_tagihan' => $grandTotal, // Angka Besar di HP
                'count'         => $allBills->count() // Badge notifikasi
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
            $bill = StudentBill::with('student')->find($request->id);
            
            if (!$bill || $bill->status == 'PAID') {
                return response()->json(['message' => 'Tagihan tidak valid atau sudah lunas'], 400);
            }
            
            $amount = $bill->amount;
            $orderIdPrefix = 'BILL'; // Kode Unik Bill
            $itemName = substr($bill->name, 0, 50);
            
            $customerDetails = [
                'first_name' => $bill->student->name,
                'phone' => $bill->student->parent_phone,
            ];

        // --- SKENARIO B: BAYAR HUTANG KANTIN (POS) ---
        } elseif ($request->type == 'POS') {
            $pos = PosOrder::with('student')->find($request->id);
            
            if (!$pos || $pos->payment_status == 'PAID') {
                return response()->json(['message' => 'Transaksi tidak valid atau sudah lunas'], 400);
            }

            $amount = $pos->total_amount;
            $orderIdPrefix = 'POS'; // Kode Unik POS
            $itemName = "Jajan Kantin " . $pos->created_at->format('d/m');

            $customerDetails = [
                'first_name' => $pos->student->name,
                'phone' => $pos->student->parent_phone,
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
            
            // Simpan token ke database (Opsional, sekedar log)
            // if ($request->type == 'POS' && isset($pos)) $pos->update(['payment_token' => $snapToken]);
            // if (($request->type == 'BILL' || $request->type == 'SPP') && isset($bill)) $bill->update(['payment_token' => $snapToken]);

            return response()->json([
                'snap_token' => $snapToken, 
                'order_id' => $orderId
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
}