<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice SPP - {{ $bill->student->name }}</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { margin: 0; size: auto; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-10 print:bg-white print:py-0">

    <div class="no-print mb-6 flex gap-3">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold shadow-lg transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak / PDF
        </button>
        <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-bold shadow-lg transition">
            Tutup
        </button>
    </div>

    <div class="bg-white w-full max-w-[210mm] min-h-[148mm] p-10 md:p-12 shadow-2xl print:shadow-none print:w-full print:max-w-none rounded-xl relative overflow-hidden">
        
        <div class="absolute top-10 right-10 opacity-10 pointer-events-none">
             <span class="text-9xl font-black text-green-600 border-8 border-green-600 rounded-xl px-4 transform -rotate-12 inline-block">
                LUNAS
             </span>
        </div>

        <div class="flex justify-between items-start border-b-2 border-gray-800 pb-6 mb-8">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-blue-900 text-white flex items-center justify-center rounded-full font-bold text-2xl">
                    TU
                </div>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 uppercase tracking-wide">SMK Unggulan</h1>
                    <p class="text-sm text-gray-600">Jl. Pendidikan No. 123, Jakarta Selatan</p>
                    <p class="text-sm text-gray-600">Telp: (021) 1234-5678 | Email: admin@sekolah.sch.id</p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-4xl font-black text-gray-200 uppercase tracking-widest">INVOICE</h2>
                <p class="text-gray-500 mt-1 font-mono">#INV-SPP-{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }}</p>
            </div>
        </div>

        <div class="flex justify-between mb-10">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Ditagihkan Kepada:</p>
                <h3 class="text-xl font-bold text-gray-800">{{ $bill->student->name }}</h3>
                <p class="text-gray-600">NIS: <span class="font-mono font-bold">{{ $bill->student->nis }}</span></p>
                <p class="text-gray-600">Kelas: {{ $bill->student->class_name }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Tanggal Bayar:</p>
                <p class="text-lg font-bold text-gray-800">{{ $bill->paid_at ? \Carbon\Carbon::parse($bill->paid_at)->translatedFormat('d F Y, H:i') : '-' }}</p>
                
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mt-4 mb-1">Metode Pembayaran:</p>
                <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-md text-sm font-bold border border-gray-200">
                    {{ $bill->payment_method ?? 'Manual' }}
                </span>
            </div>
        </div>

        <table class="w-full mb-8">
            <thead>
                <tr class="bg-gray-50 border-y border-gray-200">
                    <th class="py-3 px-4 text-left font-bold text-gray-600 uppercase text-xs tracking-wider">Deskripsi Pembayaran</th>
                    <th class="py-3 px-4 text-right font-bold text-gray-600 uppercase text-xs tracking-wider">Bulan</th>
                    <th class="py-3 px-4 text-right font-bold text-gray-600 uppercase text-xs tracking-wider">Jumlah</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr>
                    <td class="py-4 px-4 text-gray-800 font-medium">Pembayaran SPP Sekolah</td>
                    <td class="py-4 px-4 text-right text-gray-600">{{ $bill->month }}</td>
                    <td class="py-4 px-4 text-right font-bold text-gray-800">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-gray-800">
                    <td colspan="2" class="pt-4 text-right font-black text-gray-900 uppercase tracking-wide text-lg">Total Bayar</td>
                    <td class="pt-4 px-4 text-right font-black text-blue-600 text-2xl">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-16 flex justify-between items-end">
            <div class="text-sm text-gray-400 italic">
                * Bukti pembayaran ini sah dan diterbitkan secara otomatis oleh sistem.<br>
                Simpan sebagai bukti transaksi yang valid.
            </div>
            <div class="text-center">
                <p class="text-xs font-bold text-gray-400 uppercase mb-16">Petugas Tata Usaha</p>
                <div class="border-b border-gray-300 w-48 mx-auto"></div>
                <p class="font-bold text-gray-800 mt-2">{{ $bill->confirmer->name ?? 'Administrator TU' }}</p>
            </div>
        </div>
    </div>

</body>
</html>