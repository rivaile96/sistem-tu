<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * FinanceAssertionNonce
 *
 * Menyimpan jti (JWT ID) dari assertion yang sudah dipakai.
 * Mencegah replay attack — assertion hanya boleh dipakai SEKALI.
 *
 * Cleanup: row dengan expires_at < now() tidak perlu disimpan.
 */
class FinanceAssertionNonce extends Model
{
    public $timestamps = false;

    protected $table = 'finance_assertion_nonces';

    protected $fillable = [
        'jti',
        'nis',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Cek apakah jti sudah pernah dipakai.
     */
    public static function isUsed(string $jti): bool
    {
        return static::where('jti', $jti)->exists();
    }

    /**
     * Tandai jti sebagai sudah dipakai.
     */
    public static function consume(string $jti, string $nis, \DateTimeInterface $expiresAt): void
    {
        static::create([
            'jti'        => $jti,
            'nis'        => $nis,
            'expires_at' => $expiresAt,
            'created_at' => now(),
        ]);
    }

    /**
     * Hapus nonce yang sudah expired — aman untuk dijalankan berkala.
     */
    public static function pruneExpired(): int
    {
        return static::where('expires_at', '<', now())->delete();
    }
}
