<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $transaction->transaction_code }}</title>
    <style>
        /* RESET & BASIC */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Consolas', 'Courier New', monospace; /* Font struk standar */
            font-size: 12px;
            background-color: #f3f4f6; /* Abu muda buat background layar */
            color: #000;
            display: flex;
            justify-content: center;
            padding-top: 20px;
        }

        /* KERTAS STRUK (58mm Standard) */
        .ticket {
            width: 58mm; /* Lebar kertas thermal standar */
            max-width: 58mm;
            background: #fff;
            padding: 10px 5px; /* Padding kiri kanan dikit aja */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 12px;
        }
        .header h1 {
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 10px;
            color: #444;
            line-height: 1.2;
        }

        /* DIVIDER (Garis Putus) */
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        /* INFO TRANSAKSI */
        .meta-info {
            font-size: 10px;
            margin-bottom: 8px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        /* TABEL ITEM */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        th {
            text-align: left;
            border-bottom: 1px dashed #000;
            padding-bottom: 4px;
            font-weight: bold;
        }
        td {
            padding-top: 4px;
            vertical-align: top;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Kolom Harga */
        .col-qty { width: 15%; text-align: center; }
        .col-price { width: 30%; text-align: right; }

        /* TOTAL SECTION */
        .totals {
            margin-top: 8px;
            font-size: 11px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .grand-total {
            font-size: 14px;
            font-weight: 800;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #000;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
            color: #444;
        }

        /* TOMBOL PRINT (Layar Saja) */
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
        }
        .btn-print {
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-family: sans-serif;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-print:hover { background-color: #1d4ed8; }

        /* PRINT MEDIA QUERY (Settingan pas diprint) */
        @media print {
            body { 
                background: none; 
                padding: 0; 
                display: block; 
            }
            .ticket {
                width: 100%; /* Full width kertas */
                max-width: none;
                box-shadow: none;
                padding: 0;
                margin: 0;
            }
            .no-print { display: none; }
            @page {
                margin: 0;
                size: auto;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak / Print</button>
    </div>

    <div class="ticket">
        
        <div class="header">
            <h1>KOPERASI SEKOLAH</h1>
            <p>SMK Unggulan Jakarta</p>
            <p>Jl. Pendidikan No. 123, Jakarta</p>
        </div>

        <div class="divider"></div>

        <div class="meta-info">
            <div class="meta-row">
                <span>Ref:</span>
                <span>{{ $transaction->transaction_code }}</span>
            </div>
            <div class="meta-row">
                <span>Tgl:</span>
                <span>{{ $transaction->created_at->format('d/m/y H:i') }}</span>
            </div>
            <div class="meta-row">
                <span>Kasir:</span>
                <span>{{ Str::limit($transaction->user->name ?? 'Admin', 15) }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <table>
            <thead>
                <tr>
                    <th style="width: 55%">Item</th>
                    <th class="col-qty">Qty</th>
                    <th class="col-price">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->items as $item)
                @php
                    $hargaSatuan = $item->price_at_transaction;
                    $subtotal = $item->quantity * $hargaSatuan;
                @endphp
                <tr>
                    <td>{{ $item->item->name ?? 'Item Hapus' }}</td>
                    <td class="col-qty">{{ $item->quantity }}</td>
                    <td class="col-price">{{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <div class="totals">
            <div class="totals-row grand-total">
                <span>TOTAL TAGIHAN</span>
                <span>{{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            
            @if($transaction->payment_status == 'PAID')
                <div class="totals-row" style="margin-top: 4px;">
                    <span>TUNAI / BAYAR</span>
                    <span>{{ number_format($transaction->payment_amount, 0, ',', '.') }}</span>
                </div>
                
                <div class="totals-row">
                    <span>KEMBALI</span>
                    <span>{{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
                </div>
            @else
                <div class="totals-row" style="margin-top: 8px;">
                    <span>STATUS</span>
                    <span style="font-weight: bold; border: 1px solid #000; padding: 1px 4px;">BELUM LUNAS</span>
                </div>
            @endif
        </div>

        <div class="divider"></div>

        <div class="footer">
            <p>Terima Kasih atas Kunjungan Anda</p>
            <p>Barang yang dibeli tidak dapat ditukar</p>
            <p style="margin-top: 5px;">-- Layanan Sistem TU --</p>
        </div>

    </div>

</body>
</html>