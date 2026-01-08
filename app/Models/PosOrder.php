<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosOrder extends Model
{
    protected $guarded = ['id'];

    // Casting status biar enak dibaca codingannya (Opsional tapi rapi)
    // Di Laravel baru bisa pakai Enum, tapi string biasa dulu biar simpel.

    // Relasi: Transaksi milik satu User (Pembeli/Ortu)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Transaksi punya banyak detail barang
    public function items()
    {
        return $this->hasMany(PosOrderItem::class);
    }
}