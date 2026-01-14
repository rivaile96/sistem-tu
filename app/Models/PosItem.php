<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosItem extends Model
{
    // Biar gampang update stok
    protected $guarded = []; 

    // Relasi: Barang ini mungkin ada di dalam Paket Bundling
    public function bundleItems()
    {
        return $this->hasMany(PosBundleItem::class, 'pos_item_id');
    }

    // Helper (Opsional): Cek status stok
    public function getStockStatusAttribute()
    {
        if ($this->stock <= 0) return 'Habis';
        if ($this->stock <= 5) return 'Menipis';
        return 'Aman';
    }
}