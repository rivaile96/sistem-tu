<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosBundle extends Model
{
    protected $fillable = ['name', 'description', 'price', 'is_active'];

    // Relasi: Satu Paket punya banyak rincian item
    public function items()
    {
        return $this->hasMany(PosBundleItem::class);
    }
}