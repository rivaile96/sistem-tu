<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentBill extends Model
{
    protected $guarded = ['id'];

    // Relasi balik ke Siswa
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    
    // Helper buat format Rupiah langsung di model
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    // Relasi Baru: Tagihan punya banyak Item
    public function items()
    {
        return $this->hasMany(BillItem::class, 'student_bill_id');
    }

    // Helper buat warna badge status (biar view rapi)
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'PAID' => 'bg-green-100 text-green-700 border-green-200',
            'UNPAID' => 'bg-red-100 text-red-700 border-red-200',
            'PARTIAL' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            default => 'bg-gray-100 text-gray-700'
        };
    }
}