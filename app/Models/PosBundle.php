<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosBundle extends Model
{
    protected $guarded = [];

    // Relasi: Satu Paket punya banyak rincian item
    public function items()
    {
        return $this->hasMany(PosBundleItem::class);
    }
}