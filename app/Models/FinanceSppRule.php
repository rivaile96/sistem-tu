<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * FinanceSppRule — Konfigurasi aturan SPP per tahun ajaran.
 *
 * Admin mendefinisikan nominal, scope kelas, tanggal jatuh tempo,
 * dan aturan siswa baru per kombinasi tahun ajaran + jenis tagihan.
 *
 * TIDAK otomatis generate tagihan — hanya konfigurasi.
 * Billing generation adalah task terpisah.
 *
 * Catatan class_scope:
 *   NULL berarti berlaku untuk SEMUA kelas.
 *   Isi string (contoh: "10", "XI IPA") berarti hanya untuk kelas itu.
 *   Ini string-based sesuai student_profiles.class_name — tidak ada FK ke tabel kelas.
 *
 * Catatan new_student_start_offset:
 *   NULL  = ikuti aturan normal (tidak ada perlakuan khusus)
 *   0     = SPP mulai bulan yang sama dengan bulan masuk
 *   1     = SPP mulai bulan BERIKUTNYA (misalnya biaya daftar ulang sudah termasuk SPP pertama)
 *   2+    = SPP mulai N bulan setelah bulan masuk
 */
class FinanceSppRule extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount'                   => 'decimal:2',
        'is_active'                => 'boolean',
        'start_month'              => 'integer',
        'start_year'               => 'integer',
        'due_day'                  => 'integer',
        'new_student_start_offset' => 'integer',
    ];

    // Relasi ke user yang membuat
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke user yang terakhir update
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scope: hanya yang aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: filter per tahun ajaran
    public function scopeForAcademicYear($query, string $year)
    {
        return $query->where('academic_year', $year);
    }

    /**
     * Label scope kelas untuk tampilan.
     * NULL ditampilkan sebagai "Semua Kelas".
     */
    public function getClassScopeLabelAttribute(): string
    {
        return $this->class_scope ?? 'Semua Kelas';
    }

    /**
     * Label offset siswa baru untuk tampilan.
     */
    public function getNewStudentOffsetLabelAttribute(): string
    {
        return match($this->new_student_start_offset) {
            null => 'Ikuti aturan normal',
            0    => 'Mulai bulan masuk',
            1    => 'Mulai bulan berikutnya',
            default => "Mulai {$this->new_student_start_offset} bulan setelah masuk",
        };
    }
}
