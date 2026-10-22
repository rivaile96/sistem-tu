<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clazz extends Model
{
    use HasFactory;

    // 🔥 KUNCI PENTING: Kasih tau Laravel kalau tabel aslinya bernama 'classes'
    protected $table = 'classes';

    // Biar mass-assignment aman
    protected $guarded = ['id'];

    // Relasi balikan ke Student (Opsional, tapi bagus buat disiapin)
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }
}