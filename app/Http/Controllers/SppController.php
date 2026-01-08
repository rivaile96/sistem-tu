<?php

namespace App\Http\Controllers;

use App\Models\SppBill;
use App\Models\Student;
use App\Models\User; // Tambahan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; 
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;

class SppController extends Controller
{
    /**
     * 1. Halaman Daftar Tagihan
     */
    public function index(Request $request)
    {
        $query = SppBill::with(['student']);

        // Search Logic
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('class_name', 'like', "%{$search}%");
            });
        }

        // Sorting: Dahulukan yang BELUM/PENDING
        $bills = $query->orderByRaw("FIELD(status, 'PENDING', 'BELUM', 'LUNAS')")
                       ->latest()
                       ->paginate(10)
                       ->withQueryString();

        return view('spp.index', compact('bills'));
    }

    /**
     * 2. PEMBAYARAN MANUAL (Cash di TU)
     */
    public function storePayment(Request $request, $id)
    {
        $request->validate(['payment_method' => 'required|string']);

        $bill = SppBill::findOrFail($id);

        if ($bill->status === 'LUNAS') {
            return back()->with('error', 'Tagihan ini sudah lunas!');
        }

        // Gunakan Transaction biar aman
        DB::transaction(function () use ($bill, $request) {
            $bill->update([
                'status' => 'LUNAS',
                'payment_method' => $request->payment_method, // 'Cash' atau 'Transfer Manual'
                'paid_at' => now(),
                'confirmed_by' => Auth::id(), // PENTING: Catat siapa yang terima duit
            ]);
            
            // TODO: Bisa tambah Log ke tabel audit_logs di sini
        });

        return back()->with('success', "Pembayaran SPP siswa {$bill->student->name} berhasil dicatat!");
    }

    /**
     * 3. REQUEST SNAP TOKEN (Pembayaran Online)
     * Dipanggil via AJAX dari tombol "Bayar Sekarang"
     */
    public function getMidtransToken($id)
    {
        // Load data siswa & orang tua
        $bill = SppBill::with(['student', 'student.parent'])->findOrFail($id);

        // Validasi Dasar
        if ($bill->status === 'LUNAS') {
            return response()->json(['error' => 'Tagihan sudah lunas!'], 400);
        }

        // A. Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');

        // B. Generate Order ID Unik (SPP-{ID}-{RANDOM})
        // Kita pakai random string biar kalau cancel, bisa generate baru
        $orderId = 'SPP-' . $bill->id . '-' . Str::random(5);

        // C. Ambil data Customer (Orang Tua)
        $parentEmail = $bill->student->parent->email ?? 'no-email@sekolah.id';
        $parentPhone = $bill->student->parent->phone ?? '080000000000';

        // D. Parameter Payload ke Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $bill->amount,
            ],
            'customer_details' => [
                'first_name' => $bill->student->name,
                'email'      => $parentEmail,
                'phone'      => $parentPhone,
            ],
            'item_details' => [[
                'id'       => 'SPP-'.$bill->id,
                'price'    => (int) $bill->amount,
                'quantity' => 1,
                'name'     => 'SPP Bulan ' . $bill->month,
            ]]
        ];

        try {
            // E. Minta Token ke Server Midtrans
            $snapToken = Snap::getSnapToken($params);

            // F. UPDATE DATABASE (PENTING SEBELUM RETURN)
            // Simpan Order ID & Token agar Webhook nanti valid
            $bill->update([
                'midtrans_order_id' => $orderId,
                'snap_token' => $snapToken,
                'status' => 'PENDING' // Ubah status jadi pending bayar
            ]);

            return response()->json(['token' => $snapToken]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 4. Halaman Form Generate Massal
     */
    public function createGenerate()
    {
        return view('spp.generate');
    }

    /**
     * 5. Proses Generate Tagihan Massal
     */
    public function storeGenerate(Request $request)
    {
        $request->validate([
            'month' => 'required|string', 
            'amount' => 'required|numeric|min:0',
        ]);

        // Parsing Nama Bulan (2024-03 -> Maret 2024)
        try {
            $dateObj = Carbon::createFromFormat('Y-m', $request->month);
            $monthName = $dateObj->translatedFormat('F Y'); 
        } catch (\Exception $e) {
            $monthName = $request->month; 
        }

        $amount = $request->amount;
        $count = 0;
        $skipped = 0;

        $students = Student::all();

        DB::transaction(function () use ($students, $monthName, $amount, &$count, &$skipped) {
            foreach ($students as $student) {
                // Cek Duplikat
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

        $msg = "Berhasil: {$count} tagihan. Dilewati: {$skipped} (sudah ada).";
        return to_route('spp.index')->with('success', $msg);
    }

    /**
     * 6. Cetak Invoice
     */
    public function printInvoice($id)
    {
        $bill = SppBill::with('student')->findOrFail($id);
        return view('spp.invoice_print', compact('bill')); // Pastikan buat view ini nanti
    }
}