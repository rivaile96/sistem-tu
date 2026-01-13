<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentBill;
use Midtrans\Config;
use Midtrans\Snap;

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
                          ->where('parent_phone', $request->phone)
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
    // 2. DASHBOARD (Ambil Data Tagihan & Profil)
    // =================================================================
    public function getHomeData(Request $request)
    {
        $student = $request->user(); // Ambil user dari token
        
        // Ambil Tagihan Belum Lunas (Terbaru diatas)
        $unpaidBills = StudentBill::where('student_id', $student->id)
                                  ->where('status', 'UNPAID')
                                  ->latest()
                                  ->get();
        
        // Ambil Riwayat Lunas (Limit 5 terakhir)
        $historyBills = StudentBill::where('student_id', $student->id)
                                   ->where('status', 'PAID')
                                   ->latest()
                                   ->take(5)
                                   ->get();

        return response()->json([
            'student' => $student,
            // Hitung total hutang dari collection di atas
            'total_debt' => $unpaidBills->sum('amount'), 
            'unpaid_bills' => $unpaidBills,
            'history' => $historyBills
        ]);
    }

    // =================================================================
    // 3. CREATE PAYMENT (Minta Link Bayar ke Midtrans)
    // =================================================================
    public function createPayment(Request $request)
    {
        $bill = StudentBill::find($request->bill_id);
        
        // Validasi: Tagihan harus ada dan statusnya belum lunas
        if (!$bill || $bill->status == 'PAID') {
            return response()->json(['message' => 'Tagihan tidak valid atau sudah lunas'], 400);
        }

        // Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        // Buat Order ID Unik (Format: BILL-{ID_TAGIHAN}-{TIMESTAMP})
        // Contoh: BILL-8-1768284518
        $orderId = 'BILL-' . $bill->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $bill->amount,
            ],
            'customer_details' => [
                'first_name' => $bill->student->name,
                'email' => 'ortu@sekolah.id', // Bisa diganti email sekolah/ortu
                'phone' => $bill->student->parent_phone,
            ],
            'item_details' => [[
                'id' => $bill->id,
                'price' => (int) $bill->amount,
                'quantity' => 1,
                'name' => substr($bill->name, 0, 50) // Nama tagihan (Max 50 char)
            ]]
        ];

        try {
            // Minta Snap Token ke Midtrans
            $snapToken = Snap::getSnapToken($params);
            
            // Simpan token ke database (opsional, buat referensi)
            $bill->update(['payment_token' => $snapToken]);

            return response()->json([
                'snap_token' => $snapToken, 
                'order_id' => $orderId
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // =================================================================
    // 4. WEBHOOK (Dihit otomatis oleh Midtrans)
    // =================================================================
    public function callback(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        
        // 1. Validasi Signature Key (Keamanan Wajib)
        // Rumus: SHA512(order_id + status_code + gross_amount + ServerKey)
        $hashed = hash("sha512", $request->order_id.$request->status_code.$request->gross_amount.$serverKey);
        
        if ($hashed == $request->signature_key) {
            
            // 2. Cek Status Transaksi
            if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                
                // Pecah Order ID buat dapet ID Tagihan asli
                // Format: BILL-8-1768284518 -> Kita ambil angka '8'
                $orderIdParts = explode('-', $request->order_id);
                $billId = $orderIdParts[1]; 

                $bill = StudentBill::find($billId);

                if ($bill) {
                    $bill->update([
                        'status' => 'PAID',
                        'payment_method' => 'MIDTRANS', // <--- PENTING: Penanda uang masuk via Payment Gateway
                        'payment_token' => null, // Bersihkan token karena sudah lunas
                        'updated_at' => now()
                    ]);
                }
            }
        }

        // Selalu return OK 200 biar Midtrans gak ngirim ulang notifikasi
        return response()->json(['status' => 'ok']);
    }
}