<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Kolom yang boleh diisi manual (Mass Assignment)
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',   // Admin, TU, Student
        'phone',  // No HP untuk Notifikasi
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // RELASI: User (Orang Tua) punya banyak Siswa (Anak)
    public function students()
    {
        return $this->hasMany(Student::class, 'user_id');
    }
    
    // Helper untuk cek role lebih mudah: $user->hasRole('admin')
    public function hasRole($role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTu(): bool
    {
        return $this->role === 'tu';
    }

    public function isKepalaSekolah(): bool
    {
        return $this->role === 'kepala_sekolah';
    }

    /**
     * Returns true if the user can perform write/financial operations.
     * Convenience helper — do not use as the sole authorization gate.
     */
    public function canManageFinance(): bool
    {
        return in_array($this->role, ['admin', 'tu'], true);
    }
}