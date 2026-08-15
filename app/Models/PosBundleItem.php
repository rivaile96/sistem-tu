<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosBundleItem extends Model
{
    protected $fillable = ['pos_bundle_id', 'pos_item_id', 'quantity', 'price', 'subtotal'];

    // Relasi: Item ini milik paket mana?
    public function bundle()
    {
        return $this->belongsTo(PosBundle::class, 'pos_bundle_id');
    }

    // Relasi: Item ini ngambil barang apa dari Gudang (PosItem)?
    public function product()
    {
        return $this->belongsTo(PosItem::class, 'pos_item_id');
    }
}