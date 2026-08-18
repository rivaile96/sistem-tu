<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Student;
use App\Models\PosOrderItem;

class PosOrder extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'total_amount'   => 'integer',
        'payment_amount' => 'integer',
        'change_amount'  => 'integer',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    // Relasi: Transaksi dibuat oleh kasir (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Transaksi milik siswa (pembeli)
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Relasi: Transaksi punya banyak detail barang
    public function items()
    {
        return $this->hasMany(PosOrderItem::class);
    }

    // Helper: apakah transaksi ini sudah lunas?
    public function getIsPaidAttribute(): bool
    {
        return $this->payment_status === 'PAID';
    }

    // Helper: apakah transaksi ini hutang?
    public function getIsUnpaidAttribute(): bool
    {
        return $this->payment_status === 'UNPAID';
    }
}
