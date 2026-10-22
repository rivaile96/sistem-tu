<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * FinanceBillingType — Master jenis tagihan finance.
 *
 * Admin mendefinisikan jenis tagihan (SPP, GEDUNG, PONDOK, dll).
 * Code dipakai sebagai student_bills.type value saat billing generation.
 *
 * TIDAK otomatis generate tagihan — hanya konfigurasi master data.
 */
class FinanceBillingType extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_recurring' => 'boolean',
        'is_active'    => 'boolean',
        'sort_order'   => 'integer',
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

    // Scope: urut berdasarkan sort_order lalu name
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
