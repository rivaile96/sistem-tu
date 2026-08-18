<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosBundle extends Model
{
    protected $fillable = ['name', 'description', 'price', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'price'     => 'integer',
    ];

    /**
     * Satu paket punya banyak item (PosBundleItem).
     * Nama relasi canonical: items()
     */
    public function items()
    {
        return $this->hasMany(PosBundleItem::class);
    }

    /**
     * Alias: bundleItems() — agar eager load 'bundleItems.product'
     * bisa dipakai secara konsisten di controller maupun view.
     */
    public function bundleItems()
    {
        return $this->hasMany(PosBundleItem::class);
    }

    /**
     * Hitung total harga dari semua item di paket ini.
     */
    public function getTotalItemPriceAttribute(): int
    {
        return $this->items->sum(fn($item) => ($item->product->price ?? 0) * $item->quantity);
    }
}
