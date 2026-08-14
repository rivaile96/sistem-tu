<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'jurusan',
        'wali_kelas',
        'is_aktif',
    ];

    protected $casts = [
        'tingkat'  => 'integer',
        'is_aktif' => 'boolean',
    ];

    // =========================================================================
    // Constants
    // =========================================================================

    // Jenjang → tingkat maksimal
    const JENJANG = [
        'SD'  => ['min' => 1, 'max' => 6,  'label' => 'SD'],
        'MI'  => ['min' => 1, 'max' => 6,  'label' => 'MI'],
        'SMP' => ['min' => 7, 'max' => 9,  'label' => 'SMP'],
        'MTs' => ['min' => 7, 'max' => 9,  'label' => 'MTs'],
        'SMA' => ['min' => 10, 'max' => 12, 'label' => 'SMA'],
        'SMK' => ['min' => 10, 'max' => 12, 'label' => 'SMK'],
        'MA'  => ['min' => 10, 'max' => 12, 'label' => 'MA'],
    ];

    // =========================================================================
    // Relations
    // =========================================================================

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'kelas_id');
    }

    public function activeStudents(): HasMany
    {
        return $this->hasMany(Student::class, 'kelas_id')
            ->whereNotIn('status', ['keluar', 'lulus', 'graduated', 'alumni', 'pindah_keluar']);
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * Label tingkat: "Kelas 1" / "Kelas VII" / "Kelas X"
     */
    public function getTingkatLabelAttribute(): string
    {
        $jenjang = \DB::table('school_settings')->where('key', 'jenjang')->value('value') ?? 'SMA';

        // SMP/MTs → angka romawi VII, VIII, IX
        if (in_array($jenjang, ['SMP', 'MTs'])) {
            $romawi = [7 => 'VII', 8 => 'VIII', 9 => 'IX'];
            return 'Kelas ' . ($romawi[$this->tingkat] ?? $this->tingkat);
        }

        // SMA/SMK/MA → X, XI, XII
        if (in_array($jenjang, ['SMA', 'SMK', 'MA'])) {
            $romawi = [10 => 'X', 11 => 'XI', 12 => 'XII'];
            return 'Kelas ' . ($romawi[$this->tingkat] ?? $this->tingkat);
        }

        // SD/MI → 1-6
        return 'Kelas ' . $this->tingkat;
    }

    /**
     * Label lengkap: "X IPA 1" / "VII A" / "4A"
     */
    public function getLabelLengkapAttribute(): string
    {
        return $this->nama_kelas;
    }

    /**
     * Cek apakah ini tingkat akhir (akan lulus)
     */
    public function getIsTingkatAkhirAttribute(): bool
    {
        $jenjang = \DB::table('school_settings')->where('key', 'jenjang')->value('value') ?? 'SMA';
        $config  = self::JENJANG[$jenjang] ?? ['max' => 12];
        return $this->tingkat >= $config['max'];
    }

    /**
     * Kelas tujuan naik kelas (tingkat + 1, jurusan sama)
     */
    public function getKelasTujuanNaikAttribute(): ?self
    {
        if ($this->is_tingkat_akhir) return null;

        return self::where('tingkat', $this->tingkat + 1)
            ->where('jurusan', $this->jurusan)
            ->where('is_aktif', true)
            ->first();
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    public function scopeByTingkat($query, int $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Tingkat maksimal berdasarkan jenjang sekolah
     */
    public static function tingkatMaksimal(): int
    {
        $jenjang = \DB::table('school_settings')->where('key', 'jenjang')->value('value') ?? 'SMA';
        return self::JENJANG[$jenjang]['max'] ?? 12;
    }

    /**
     * Tingkat minimal berdasarkan jenjang sekolah
     */
    public static function tingkatMinimal(): int
    {
        $jenjang = \DB::table('school_settings')->where('key', 'jenjang')->value('value') ?? 'SMA';
        return self::JENJANG[$jenjang]['min'] ?? 10;
    }
}
