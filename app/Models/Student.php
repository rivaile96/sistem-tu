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
    protected $table = 'students';

    // Kolom yang aman diisi massal (semua kecuali ID)
    protected $guarded = ['id'];

    // Casting tipe data otomatis
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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