<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rombel extends Model
{
    protected $fillable = [
        'tahun_ajaran_id',
        'kelas_id',
        'nama_rombel',
        'wali_kelas',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function studentRombels(): HasMany
    {
        return $this->hasMany(StudentRombel::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_rombels')
                    ->withTimestamps();
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    public function scopeByTahunAjaran($query, int $tahunAjaranId)
    {
        return $query->where('tahun_ajaran_id', $tahunAjaranId);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getJumlahSiswaAttribute(): int
    {
        return $this->studentRombels()->count();
    }

    public function getLabelAttribute(): string
    {
        return $this->nama_rombel . ' (' . ($this->tahunAjaran->nama ?? '-') . ')';
    }
}
