<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SppBill extends Model
{
    // Penting: Membuka kunci agar semua kolom bisa diisi
    protected $guarded = ['id'];

    // Relasi: Tagihan ini milik siapa? (Ke tabel Students)
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}