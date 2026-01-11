<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi #{{ $bill->id }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; color: #333; padding: 20px; }
        .container { border: 2px dashed #333; padding: 30px; max-width: 800px; margin: 0 auto; position: relative; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 2px; }
        .header p { margin: 5px 0 0; font-size: 14px; }
        .content { line-height: 2.5; font-size: 14px; }
        .row { display: flex; }
        .label { width: 180px; font-weight: bold; }
        .value { flex: 1; border-bottom: 1px dotted #999; padding-left: 10px; }
        .terbilang { background: #eee; padding: 5px 10px; font-style: italic; font-weight: bold; text-transform: capitalize; border: 1px solid #ccc; margin-top: 5px; }
        .footer { margin-top: 30px; display: flex; justify-content: space-between; text-align: center; }
        .amount-box { border: 2px solid #333; padding: 10px 20px; font-size: 20px; font-weight: bold; background: #fff; transform: rotate(-2deg); box-shadow: 3px 3px 0 #ccc; }
        .ttd { margin-top: 60px; font-weight: bold; text-decoration: underline; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-15deg); font-size: 100px; opacity: 0.1; font-weight: bold; z-index: -1; text-transform: uppercase; color: red; border: 5px solid red; padding: 10px 50px; }
        @media print { @page { size: landscape; margin: 0; } body { margin: 0; } .container { border: none; } }
    </style>
</head>
<body onload="window.print()">

    <div class="container">
        <div class="watermark">LUNAS</div>

        <div class="header">
            <h1>Kwitansi Pembayaran</h1>
            <p>SMK INDONESIA MAJU - SISTEM TATA USAHA</p>
        </div>

        <div class="content">
            <div class="row">
                <span class="label">No. Kwitansi</span>: 
                <span class="value">KW-{{ date('Ymd') }}-{{ str_pad($bill->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="row">
                <span class="label">Telah Terima Dari</span>: 
                <span class="value"><b>{{ $bill->student->name }}</b> ({{ $bill->student->class_name }})</span>
            </div>
            <div class="row">
                <span class="label">Uang Sejumlah</span>: 
                <span class="value terbilang"># {{ $terbilang }} #</span>
            </div>
            <div class="row">
                <span class="label">Untuk Pembayaran</span>: 
                <span class="value">{{ $bill->name }} ({{ $bill->type }})</span>
            </div>
        </div>

        <div class="footer">
            <div style="margin-top: 20px;">
                <div class="amount-box">{{ $bill->formatted_amount }}</div>
            </div>
            <div>
                <p>{{ date('d F Y') }}</p>
                <div class="ttd">Bendahara Sekolah</div>
                <p style="font-size: 10px; margin-top: 5px;">*Simpan kwitansi ini sebagai bukti pembayaran yang sah.</p>
            </div>
        </div>
    </div>

</body>
</html>