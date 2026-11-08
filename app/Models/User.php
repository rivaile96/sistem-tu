<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Kolom yang boleh diisi manual (Mass Assignment)
     */
    protected $fillable = [
        'name',
        'username', // NIS — cross-system login identifier (diisi saat sync dari Kaffah)
        'email',
        'password',
        'pin',
        'role',   // Admin, TU, Student
        'is_active',
        'phone',  // No HP untuk Notifikasi
    ];

    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // RELASI: User (Orang Tua) punya banyak Siswa (Anak)
    public function students()
    {
        return $this->hasMany(Student::class, 'user_id');
    }

    // RELASI: User (Siswa) punya satu Student Profile
    public function studentProfile()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    /**
     * Kembalikan seluruh profil siswa (anak) yang berhak diakses oleh akun ini.
     * Mendukung relasi 1-ke-1 (student_profiles.user_id) maupun 1-ke-N wali murid
     * (student_profiles.user_id = users.id atau student_profiles.parent_phone = users.phone).
     */
    public function authorizedStudents(): \Illuminate\Support\Collection
    {
        $phone = trim((string) ($this->phone ?? ''));

        return Student::query()
            ->where(function ($query) use ($phone) {
                $query->where('user_id', $this->id);

                if ($phone !== '' && \Illuminate\Support\Facades\Schema::hasColumn('student_profiles', 'parent_phone')) {
                    $query->orWhere('parent_phone', $phone);
                }
            })
            ->orderBy('id')
            ->get();
    }
    
    // Helper untuk cek role lebih mudah: $user->hasRole('admin')
    public function hasRole($role)
    {
        return $this->role === $role;
    }
}