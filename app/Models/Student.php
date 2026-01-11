<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $guarded = ['id'];

    // Relasi ke Transaksi POS (Jajan/Kantin)
    public function posOrders()
    {
        return $this->hasMany(PosOrder::class);
    }

    // [BARU] Relasi ke Tagihan Sekolah (SPP, Gedung, dll)
    public function bills()
    {
        return $this->hasMany(StudentBill::class)->latest();
    }
    
    // [BARU] Helper Hitung Total Tunggakan (Semua jenis)
    public function getTotalDebtAttribute()
    {
        // Hutang POS (Kantin) + Hutang Tagihan (SPP/Gedung)
        $posDebt = $this->posOrders()->where('payment_status', 'UNPAID')->sum('total_amount');
        $billDebt = $this->bills()->where('status', 'UNPAID')->sum('amount');
        
        return $posDebt + $billDebt;
    }
}