<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    protected $guarded = ['id']; // Semua kolom boleh diisi kecuali ID

    // RELASI: Siswa milik satu Orang Tua (User)
    public function parent()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // RELASI: Siswa punya banyak tagihan SPP
    public function bills()
    {
        return $this->hasMany(SppBill::class);
    }
}