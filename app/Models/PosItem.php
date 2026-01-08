<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosItem extends Model
{
    protected $guarded = ['id'];

    // Helper: Jika ada gambar pakai gambar itu, jika tidak pakai placeholder
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return 'https://placehold.co/400?text=No+Image'; // Gambar default sementara
    }
}