<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi #{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }} - {{ $bill->student->name }}</title>
    <style>
        /* --- RESET & BASIC SETUP --- */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Font Modern */
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            color: #333;
            -webkit-print-color-adjust: exact; /* Wajib biar warna background ikut keprint */
            print-color-adjust: exact; 
        }

        .page-container {
            width: 210mm; /* Lebar A4 */
            min-height: 297mm;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        /* --- HEADER DESIGN (Miring Biru) --- */
        .header-bg {
            background-color: #0084ff; /* Warna Biru Utama */
            height: 150px;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 0;
            /* Membuat efek miring ala desain grafis */
            clip-path: polygon(0 0, 100% 0, 100% 70%, 0 100%);
        }

        .header-content {
            position: relative;
            z-index: 1;
            padding: 40px 50px 0 50px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            color: white;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo-img {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            padding: 5px;
            object-fit: contain;
        }
        .school-identity h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .school-identity p {
            margin: 5px 0 0 0;
            font-size: 12px;
            opacity: 0.9;
            max-width: 300px;
            line-height: 1.4;
        }

        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            font-size: 36px;
            margin: 0;
            font-weight: 900;
            text-transform: uppercase;
            opacity: 0.9;
        }
        .invoice-number {
            font-size: 14px;
            margin-top: 5px;
            font-weight: bold;
        }

        /* --- INFO SECTION (FROM & TO) --- */
        .info-section {
            display: flex;
            justify-content: space-between;
            padding: 40px 50px 20px 50px;
            margin-top: 20px;
        }
        .info-box {
            width: 45%;
        }
        .info-label {
            font-size: 11px;
            font-weight: bold;
            color: #0084ff;
            text-transform: uppercase;
            margin-bottom: 8px;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
            display: block;
        }
        .info-value {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .info-sub {
            font-size: 13px;
            color: #666;
        }

        /* --- STATUS STAMP (LUNAS/BELUM) --- */
        .status-stamp {
            position: absolute;
            top: 180px;
            right: 50px;
            font-size: 20px;
            font-weight: bold;
            padding: 10px 30px;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 2px;
            transform: rotate(-10deg);
            border: 3px solid;
            opacity: 0.8;
        }
        .status-paid {
            color: #10b981; /* Hijau */
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.1);
        }
        .status-unpaid {
            color: #ef4444; /* Merah */
            border-color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        /* --- TABLE DESIGN --- */
        .table-container {
            padding: 0 50px;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            box-shadow: 0 0 0 1px #eee; /* Thin border */
            border-radius: 8px;
        }
        thead {
            background-color: #0084ff;
            color: white;
        }
        th {
            padding: 12px 15px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:nth-child(even) {
            background-color: #f9fbff;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* --- TOTALS & TERBILANG --- */
        .total-section {
            padding: 20px 50px;
            display: flex;
            justify-content: flex-end;
        }
        .total-box {
            width: 40%;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 13px;
        }
        .total-row.final {
            font-size: 18px;
            font-weight: 800;
            color: #0084ff;
            border-top: 2px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
            margin-bottom: 0;
        }
        
        .terbilang-box {
            padding: 0 50px;
            margin-top: -10px;
            font-size: 12px;
            font-style: italic;
            color: #555;
        }

        /* --- FOOTER & SIGNATURE --- */
        .footer {
            padding: 40px 50px;
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .thank-you {
            font-size: 24px;
            font-weight: 800;
            color: #ddd;
            text-transform: uppercase;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-date {
            font-size: 12px;
            margin-bottom: 5px;
        }
        .signature-role {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px; /* Jarak ke gambar TTD */
        }
        .signature-img-container {
            height: 80px; /* Tinggi Fix buat TTD */
            display: flex;
            align-items: flex-end; /* TTD nempel ke bawah */
            justify-content: center;
            margin-bottom: 5px;
        }
        .signature-img {
            max-height: 80px;
            max-width: 150px;
            object-fit: contain;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 14px;
        }

        /* --- TOMBOL PRINT --- */
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .btn-print {
            background: #333;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: 0.3s;
        }
        .btn-print:hover { background: #000; transform: translateY(-2px); }

        /* PRINT MODE */
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .page-container { box-shadow: none; margin: 0; width: 100%; height: auto; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <div class="page-container">
        
        <div class="header-bg"></div>
        
        <div class="header-content">
            <div class="logo-section">
                @if(isset($school['school_logo']))
                    <img src="{{ asset('storage/'.$school['school_logo']) }}" class="logo-img" alt="Logo">
                @else
                    <div class="logo-img" style="display:flex; justify-content:center; align-items:center; color:#0084ff; font-weight:bold;">LOGO</div>
                @endif

                <div class="school-identity">
                    <h1>{{ $school['school_name'] ?? 'SDIT Kaffah Islamic School' }}</h1>
                    <p>{{ $school['school_address'] ?? 'Jl. Cempaka Putih Jakarta 1180' }}</p>
                    <p>Telp: {{ $school['school_phone'] ?? '021-555-0199' }}</p>
                </div>
            </div>

            <div class="invoice-title">
                <h2>KWITANSI</h2>
                <div class="invoice-number">NO: #{{ date('Y') }}/{{ str_pad($bill->id, 5, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        @if($bill->status == 'PAID' || $bill->status == 'LUNAS')
            <div class="status-stamp status-paid">LUNAS</div>
        @else
            <div class="status-stamp status-unpaid">BELUM LUNAS</div>
        @endif

        <div class="info-section">
            <div class="info-box">
                <span class="info-label">Diterima Dari (Siswa)</span>
                <div class="info-value">{{ $bill->student->name }}</div>
                <div class="info-sub">NIS: {{ $bill->student->nis }}</div>
                <div class="info-sub">Kelas: {{ $bill->student->class_name ?? '-' }}</div>
            </div>

            <div class="info-box" style="text-align: right;">
                <span class="info-label">Detail Pembayaran</span>
                {{-- Phase 2.4: use paid_at as canonical payment date.
                     Do NOT fabricate a date from updated_at for historical records. --}}
                <div class="info-value">Tgl. Bayar:
                    @if($bill->paid_at)
                        {{ \Carbon\Carbon::parse($bill->paid_at)->translatedFormat('d F Y') }}
                    @else
                        <span style="color:#999; font-style:italic;">Tanggal pembayaran tidak tersedia</span>
                    @endif
                </div>
                <div class="info-sub">Metode: <span style="text-transform: uppercase;">{{ $bill->payment_method ?? 'Tunai' }}</span></div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th>Deskripsi Pembayaran</th>
                        <th width="25%" class="text-right">Jumlah (IDR)</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($bill->items) && count($bill->items) > 0)
                        @foreach($bill->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong style="color:#0084ff">{{ $item->item_name }}</strong>
                                @if($item->quantity > 1) 
                                    <br><span style="font-size:11px; color:#777;">{{ $item->quantity }} x @ {{ number_format($item->amount, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    @else
                    <tr>
                            <td>1</td>
                            <td>
                                <strong style="color:#0084ff">{{ $bill->name }}</strong>
                                <br><span style="font-size:11px; color:#777;">Pembayaran Sekolah</span>
                            </td>
                            <td class="text-right">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="total-section">
            <div class="total-box">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($bill->amount, 0, ',', '.') }}</span>
                </div>
                <div class="total-row final">
                    <span>TOTAL BAYAR</span>
                    <span>Rp {{ number_format($bill->amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="terbilang-box">
            <strong>Terbilang:</strong> <span style="text-transform: capitalize; background: #eee; padding: 2px 5px; border-radius: 3px;"># {{ $terbilang ?? 'Nominal Rupiah' }} #</span>
        </div>

        <div class="footer">
            <div class="thank-you">
                TERIMA KASIH <br>
                <span style="font-size:10px; color:#aaa; font-weight:normal;">Bukti pembayaran ini sah dan diterbitkan oleh sistem komputer.</span>
            </div>
            
            <div class="signature-box">
                <div class="signature-date">Jakarta, {{ date('d F Y') }}</div>
                <div class="signature-role">Bendahara / Ka. TU</div>
                
                <div class="signature-img-container">
                    @if(isset($school['school_signature']))
                        <img src="{{ asset('storage/'.$school['school_signature']) }}" class="signature-img" alt="Tanda Tangan">
                    @else
                        <div style="height: 60px;"></div> 
                    @endif
                </div>
                
                <div class="signature-name">{{ $school['head_of_admin'] ?? 'Admin TU' }}</div>
            </div>
        </div>

    </div>

</body>
</html>