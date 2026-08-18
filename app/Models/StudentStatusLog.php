<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentStatusLog extends Model
{
    protected $table = 'student_status_logs';
    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function diubahOleh()
    {
        return $this->belongsTo(\App\Models\User::class, 'diubah_oleh');
    }

    /**
     * Alias 'changedBy' untuk view/komponen yang memakai nama generik.
     * Menggantikan alias lama 'statusChangedBy' yang bentrok dengan
     * relasi Student::statusChangedBy() (FK berbeda: status_changed_by).
     * Keduanya menuju User, tapi lewat kolom FK yang berlainan.
     */
    public function changedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'diubah_oleh');
    }

    public function getStatusLamaLabelAttribute(): string
    {
        return self::statusLabel($this->status_lama);
    }

    public function getStatusBaruLabelAttribute(): string
    {
        return self::statusLabel($this->status_baru);
    }

    public static function statusLabel(?string $status): string
    {
        return match ($status) {
            'active'         => 'Siswa Aktif',
            'pindah_masuk'   => 'Pindah Masuk',
            'pindah_keluar'  => 'Pindah Keluar',
            'keluar'         => 'Keluar / DO',
            'graduated'      => 'Lulus',
            'alumni'         => 'Alumni',
            'calon_siswa'    => 'Calon Siswa',
            default          => ucfirst((string) $status),
        };
    }
}
