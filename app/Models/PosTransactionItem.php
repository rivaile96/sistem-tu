<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosTransactionItem extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function item() {
        return $this->belongsTo(PosItem::class, 'pos_item_id');
    }
}