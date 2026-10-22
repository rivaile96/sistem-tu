<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentChannel extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'logo',
        'is_active',
    ];
}
