<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosBundleItem extends Model
{
    // Hapus 'price' dan 'subtotal' — kolom ini tidak ada di DB (pos_bundle_items).
    // Harga diambil langsung dari PosItem->price saat dibutuhkan.
    protected $fillable = ['pos_bundle_id', 'pos_item_id', 'quantity'];

    /**
     * Item ini milik paket mana?
     */
    public function bundle()
    {
        return $this->belongsTo(PosBundle::class, 'pos_bundle_id');
    }

    /**
     * Item ini merujuk ke barang mana di master PosItem?
     * Nama: product() — dipakai di seluruh controller dan view.
     */
    public function product()
    {
        return $this->belongsTo(PosItem::class, 'pos_item_id');
    }

    /**
     * Alias posItem() agar eager load 'items.posItem' tidak crash.
     * Beberapa bagian controller lama masih pakai nama ini.
     */
    public function posItem()
    {
        return $this->belongsTo(PosItem::class, 'pos_item_id');
    }

    /**
     * Subtotal item ini (qty × harga saat ini dari master).
     */
    public function getSubtotalAttribute(): int
    {
        return ($this->product->price ?? 0) * $this->quantity;
    }
}
