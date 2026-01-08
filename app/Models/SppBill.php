<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SppBill extends Model
{
    protected $guarded = ['id'];

    // Agar kolom tanggal otomatis jadi Object Carbon (bisa diformat .format('d M Y'))
    protected $casts = [
        'paid_at' => 'datetime',
    ];

    // Relasi ke Siswa
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Relasi ke Staff TU yang konfirmasi (jika bayar manual)
    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}