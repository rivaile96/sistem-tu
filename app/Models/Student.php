<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Opsional, tapi bagus ada

class Student extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi: Siswa punya banyak tagihan
    public function bills()
    {
        return $this->hasMany(SppBill::class);
    }
}