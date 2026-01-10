<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Student;
use App\Models\PosOrderItem;

class PosOrder extends Model
{
    protected $guarded = ['id'];

    // Casting status biar enak dibaca codingannya (Opsional tapi rapi)
    // Di Laravel baru bisa pakai Enum, tapi string biasa dulu biar simpel.

    // Relasi: Transaksi milik satu User (Kasir / Pembuat transaksi)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ============================
    // 🔥 RELASI BARU: SISWA (PEMBELI)
    // ============================
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Relasi: Transaksi punya banyak detail barang
    public function items()
    {
        return $this->hasMany(PosOrderItem::class);
    }
}
