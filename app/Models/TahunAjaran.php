<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_aktif',
    ];

    protected $casts = [
        'tanggal_mulai'    => 'date',
        'tanggal_selesai'  => 'date',
        'is_aktif'         => 'boolean',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function rombels(): HasMany
    {
        return $this->hasMany(Rombel::class);
    }

    public function studentRombels(): HasMany
    {
        return $this->hasMany(StudentRombel::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public static function aktifSekarang(): ?self
    {
        return self::where('is_aktif', true)->first();
    }

    /**
     * Set tahun ajaran ini sebagai aktif, nonaktifkan yang lain
     */
    public function setAktif(): void
    {
        self::query()->update(['is_aktif' => false]);
        $this->update(['is_aktif' => true]);
    }
}
