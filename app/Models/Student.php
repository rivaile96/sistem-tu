<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $guarded = ['id'];

    // Relasi: Satu siswa bisa punya banyak transaksi POS
    public function posOrders()
    {
        return $this->hasMany(PosOrder::class);
    }
}