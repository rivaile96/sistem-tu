<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <--- PENTING: Untuk Login API Android

class Student extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    // Nama tabel di database
    // NIS-identity: tabel ini adalah student_profiles di production PostgreSQL.
    // Nama tabel 'students' adalah alias lama — production tidak memiliki tabel 'students'.
    protected $table = 'student_profiles';

    // Kolom yang aman diisi massal (semua kecuali ID)
    protected $guarded = ['id'];

    // Casting tipe data otomatis
    protected $casts = [
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'synced_at'   => 'datetime',
        // enrolled_at: tanggal resmi enrollment, bukan created_at/synced_at.
        // NULL = provisioned tapi belum official enrolled.
        // Finance HARUS cek null sebelum menggunakan.
        'enrolled_at' => 'datetime',
    ];

    // ==========================================
    // 🔗 RELASI (RELATIONSHIPS)
    // ==========================================

    /**
     * Relasi ke Tagihan Sekolah (SPP, Gedung, Seragam, dll)
     * Mengambil data terbaru dulu (latest)
     */
    public function bills()
    {
        return $this->hasMany(StudentBill::class, 'student_id')->latest();
    }

    /**
     * Relasi ke Riwayat Jajan Kantin (POS)
     */
    public function posOrders()
    {
        return $this->hasMany(PosOrder::class, 'student_id')->latest();
    }

    /**
     * Relasi ke User TU (login account)
     * student_profiles.user_id → users.id
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    /**
     * Accessor: $student->name → ambil dari users.name
     * student_profiles tidak punya kolom name — nama disimpan di tabel users.
     */
    public function getNameAttribute(): ?string
    {
        return $this->user?->name;
    }

    /**
     * Apakah siswa sudah official enrolled?
     *
     * Finance WAJIB menggunakan method ini — jangan cek enrolled_at langsung
     * agar business rule tidak tersebar di seluruh codebase.
     *
     * NULL enrolled_at = provisioned tapi belum resmi enrolled.
     * Finance harus skip atau handle secara eksplisit.
     */
    public function isEnrolled(): bool
    {
        return $this->enrolled_at !== null;
    }

    /**
     * Apakah siswa baru untuk periode billing tertentu?
     *
     * Siswa dianggap "baru" jika enrolled_at jatuh dalam bulan/tahun yang sama
     * dengan billing period yang ditanyakan.
     *
     * Finance billing engine akan menggunakan ini bersama new_student_start_offset
     * dari FinanceSppRule untuk menentukan kapan billing dimulai.
     *
     * @param int $billingMonth 1-12
     * @param int $billingYear  contoh: 2026
     */
    public function isNewStudentForPeriod(int $billingMonth, int $billingYear): bool
    {
        if (!$this->isEnrolled()) {
            return false; // belum enrolled = tidak bisa diklasifikasikan
        }

        return $this->enrolled_at->year === $billingYear
            && $this->enrolled_at->month === $billingMonth;
    }

    // ==========================================
    // ⚡ AKSESOR PINTAR (ACCESSORS)
    // ==========================================

    /**
     * Hitung Total Hutang Otomatis
     * Cara panggil: $student->total_debt
     */
    public function getTotalDebtAttribute()
    {
        // 1. Hitung tunggakan Tagihan (SPP dll)
        $billDebt = $this->bills()->where('status', 'UNPAID')->sum('amount');
        
        // 2. Hitung hutang Kantin (Jika status POS 'UNPAID')
        $posDebt = $this->posOrders()->where('payment_status', 'UNPAID')->sum('total_amount');

        return $billDebt + $posDebt;
    }

    /**
     * Format Rupiah Total Hutang
     * Cara panggil: $student->formatted_total_debt
     */
    public function getFormattedTotalDebtAttribute()
    {
        return 'Rp ' . number_format($this->total_debt, 0, ',', '.');
    }

    /**
     * Warna Badge Status Siswa (Untuk UI)
     * Cara panggil: $student->status_color
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'active' => 'bg-green-100 text-green-700 border-green-200',
            'graduated' => 'bg-blue-100 text-blue-700 border-blue-200',
            'dropped_out' => 'bg-red-100 text-red-700 border-red-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    /**
     * Label Status Siswa (Huruf Kapital Rapih)
     * Cara panggil: $student->status_label
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'active' => 'Siswa Aktif',
            'graduated' => 'Lulus',
            'dropped_out' => 'Keluar / DO',
            default => ucfirst($this->status),
        };
    }
}