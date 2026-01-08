<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOrderItem extends Model
{
    protected $guarded = ['id'];

    // Relasi balik ke Order Utama
    public function order()
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    // Relasi ke Master Barang (untuk ambil nama/kategori saat laporan)
    public function item() // atau product()
    {
        return $this->belongsTo(PosItem::class, 'pos_item_id');
    }
}