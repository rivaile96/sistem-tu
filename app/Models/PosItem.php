<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosItem extends Model
{
    protected $fillable = ['name', 'category', 'stock', 'price', 'image'];

    protected $casts = [
        'price'     => 'integer',
        'stock'     => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Barang ini ada di dalam bundle mana saja.
     */
    public function bundleItems()
    {
        return $this->hasMany(PosBundleItem::class, 'pos_item_id');
    }

    /**
     * Barang ini pernah masuk transaksi mana saja.
     */
    public function orderItems()
    {
        return $this->hasMany(PosOrderItem::class, 'pos_item_id');
    }

    /**
     * Status stok: Habis / Menipis / Aman
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0)  return 'Habis';
        if ($this->stock <= 5)  return 'Menipis';
        return 'Aman';
    }

    /**
     * CSS badge class untuk status stok.
     */
    public function getStockBadgeClassAttribute(): string
    {
        return match($this->stock_status) {
            'Habis'   => 'bg-red-100 text-red-700 border-red-200',
            'Menipis' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            default   => 'bg-green-100 text-green-700 border-green-200',
        };
    }
}
