<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTransaction extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function items() {
        return $this->hasMany(PosTransactionItem::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}