<?php

namespace App\Http\Controllers;

use App\Models\SppBill;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Library untuk manipulasi tanggal
use Midtrans\Config;
use Midtrans\Snap;

class SppController extends Controller
{
    /**
     * 1. Menampilkan Daftar Tagihan SPP (Page 4 - Daftar Tagihan)
     * Fitur: Pagination, Sorting Status (Belum Lunas di atas), Searching.
     */
    public function index(Request $request)
    {
        // Mulai Query
        $query = SppBill::with('student');

        // Logic Pencarian (Nama Siswa, NIS, atau Kelas)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('class_name', 'like', "%{$search}%");
            });
        }

        // Logic Sorting: Prioritaskan yang BELUM/PENDING di atas, baru LUNAS
        $bills = $query->orderByRaw("FIELD(status, 'PENDING', 'BELUM', 'LUNAS')")
                       ->latest()
                       ->paginate(10)
                       ->withQueryString();

        return view('spp.index', compact('bills'));
    }

    /**
     * 2. Proses Pembayaran Manual (Page 7 - Tebus Barang/Bayar di TU)
     */
    public function storePayment(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|string',
        ]);

        $bill = SppBill::findOrFail($id);

        if ($bill->status === 'LUNAS') {
            return back()->with('error', 'Tagihan ini sudah lunas sebelumnya!');
        }

        DB::transaction(function () use ($bill, $request) {
            $bill->update([
                'status' => 'LUNAS',
                'payment_method' => $request->payment_method,
                'paid_at' => now(),
            ]);
        });

        return back()->with('success', "Pembayaran SPP atas nama {$bill->student->name} berhasil dicatat!");
    }

    /**
     * 3. Halaman Form Generate Tagihan Massal
     */
    public function createGenerate()
    {
        return view('spp.generate'); 
    }

    /**
     * 4. Proses Generate Tagihan Massal (Logic Diperbaiki)
     * Mengubah input '2024-03' menjadi 'Maret 2024' dan mencegah duplikat.
     */
    public function storeGenerate(Request $request)
    {
        $request->validate([
            'month' => 'required|string', // Format dari input type="month" adalah "YYYY-MM"
            'amount' => 'required|numeric|min:0',
        ]);

        // 1. Ubah format tanggal jadi lebih enak dibaca (Contoh: "Maret 2024")
        try {
            // Pastikan app locale di config/app.php sudah 'id' agar bahasa Indonesia
            $dateObj = Carbon::createFromFormat('Y-m', $request->month);
            $monthName = $dateObj->translatedFormat('F Y'); 
        } catch (\Exception $e) {
            // Fallback jika gagal parse
            $monthName = $request->month; 
        }

        $amount = $request->amount;
        $count = 0;
        $skipped = 0;

        $students = Student::all();

        DB::transaction(function () use ($students, $monthName, $amount, &$count, &$skipped) {
            foreach ($students as $student) {
                // 2. Cek Anti Duplikat: Apakah siswa ini sudah punya tagihan di bulan tersebut?
                $exists = SppBill::where('student_id', $student->id)
                                 ->where('month', $monthName)
                                 ->exists();

                if (!$exists) {
                    SppBill::create([
                        'student_id' => $student->id,
                        'month' => $monthName,
                        'amount' => $amount,
                        'status' => 'BELUM',
                    ]);
                    $count++;
                } else {
                    $skipped++;
                }
            }
        });

        $message = "Berhasil membuat {$count} tagihan untuk bulan {$monthName}.";
        if ($skipped > 0) {
            $message .= " ({$skipped} siswa dilewati karena sudah ada tagihan).";
        }

        return to_route('spp.index')->with('success', $message);
    }

    /**
     * 5. Cetak Invoice (Placeholder)
     */
    public function printInvoice($id)
    {
        $bill = SppBill::with('student')->findOrFail($id);
        return back()->with('info', 'Fitur cetak sedang dalam pengembangan.');
    }

    /**
     * 6. Request Snap Token ke Midtrans (Untuk Pembayaran Online)
     */
    public function getMidtransToken($id)
    {
        $bill = SppBill::with('student')->findOrFail($id);

        // 1. Konfigurasi Midtrans
        // Mengambil dari config/services.php
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized  = config('services.midtrans.is_sanitized');
        Config::$is3ds        = config('services.midtrans.is_3ds');

        // 2. Buat Order ID Unik (Format: SPP-{ID}-{TIMESTAMP})
        $orderId = 'SPP-' . $bill->id . '-' . time();

        // 3. Parameter Transaksi
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $bill->amount,
            ],
            'customer_details' => [
                'first_name' => $bill->student->name,
                'last_name'  => $bill->student->class_name,
                'email'      => 'siswa@sekolah.com', // Email dummy (opsional)
                'phone'      => '08123456789',       // No HP dummy (opsional)
            ],
            'item_details' => [
                [
                    'id'       => 'SPP-' . $bill->month,
                    'price'    => $bill->amount,
                    'quantity' => 1,
                    'name'     => 'SPP ' . $bill->month,
                ]
            ]
        ];

        // 4. Request Snap Token
        try {
            $snapToken = Snap::getSnapToken($params);
            return response()->json(['token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}