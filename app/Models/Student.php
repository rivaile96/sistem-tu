<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Kolom yang dipakai sebagai "username" saat Auth::guard('siswa')->attempt()
     */
    public function getAuthIdentifierName(): string
    {
        return 'nis';
    }

    /**
     * Password siswa = tanggal lahir format dmy (ddmmyy, 6 digit)
     * Kita TIDAK hash ini karena login divalidasi manual di controller.
     * Method ini wajib ada agar Authenticatable contract terpenuhi.
     */
    public function getAuthPassword(): string
    {
        return \Carbon\Carbon::parse($this->birth_date)->format('dmy');
    }

    protected $table = 'students';
    protected $guarded = ['id'];

    protected $casts = [
        'birth_date'         => 'date',
        'status_changed_at'  => 'datetime',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    // Status yang tersedia
    const STATUSES = [
        'active'        => 'Siswa Aktif',
        'pindah_masuk'  => 'Pindah Masuk',
        'pindah_keluar' => 'Pindah Keluar',
        'keluar'        => 'Keluar / DO',
        'graduated'     => 'Lulus',
        'alumni'        => 'Alumni',
        'calon_siswa'   => 'Calon Siswa',
    ];

    // ==========================================
    // RELASI
    // ==========================================

    public function bills()
    {
        return $this->hasMany(StudentBill::class, 'student_id')->latest();
    }

    public function posOrders()
    {
        return $this->hasMany(PosOrder::class, 'student_id')->latest();
    }

    public function statusLogs()
    {
        return $this->hasMany(StudentStatusLog::class, 'student_id')->latest();
    }

    public function statusChangedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'status_changed_by');
    }

    public function kelas()
    {
        return $this->belongsTo(\App\Models\Kelas::class, 'kelas_id');
    }

    public function studentRombels()
    {
        return $this->hasMany(StudentRombel::class);
    }

    /**
     * Rombel aktif siswa di tahun ajaran tertentu
     */
    public function rombelAktif(?int $tahunAjaranId = null)
    {
        $query = $this->hasOne(StudentRombel::class)->latest();
        if ($tahunAjaranId) {
            $query->where('tahun_ajaran_id', $tahunAjaranId);
        }
        return $query;
    }

    public function rombels()
    {
        return $this->belongsToMany(Rombel::class, 'student_rombels')
                    ->withTimestamps();
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeAktif($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    public function getTotalDebtAttribute()
    {
        $billDebt = $this->bills()->where('status', 'UNPAID')->sum('amount');
        $posDebt  = $this->posOrders()->where('payment_status', 'UNPAID')->sum('total_amount');
        return $billDebt + $posDebt;
    }

    public function getFormattedTotalDebtAttribute()
    {
        return 'Rp ' . number_format($this->total_debt, 0, ',', '.');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active'        => 'bg-green-100 text-green-700 border-green-200',
            'pindah_masuk'  => 'bg-blue-100 text-blue-700 border-blue-200',
            'pindah_keluar' => 'bg-orange-100 text-orange-700 border-orange-200',
            'keluar'        => 'bg-red-100 text-red-700 border-red-200',
            'graduated'     => 'bg-purple-100 text-purple-700 border-purple-200',
            'alumni'        => 'bg-indigo-100 text-indigo-700 border-indigo-200',
            'calon_siswa'   => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            default         => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getIsAktifAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-',
        };
    }
}
