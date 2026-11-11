<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tu:create-admin
                            {--name= : Nama lengkap administrator}
                            {--email= : Alamat email aktif}
                            {--username= : Username login unik}
                            {--role=admin : Role pengguna (admin atau tu)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bootstrap akun administrator / staf TU awal secara aman dan interaktif';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== BOOTSTRAP AKUN ADMINISTRATOR SISTEM TU ===');

        if (app()->environment('production')) {
            $this->error('PERINGATAN KEAMANAN: Command ini tidak dapat dijalankan di production tanpa konfirmasi eksplisit.');
            if (!$this->confirm('Apakah Anda yakin ingin melanjutkan di environment production?')) {
                return self::FAILURE;
            }
        }

        // 1. Ambil input secara interaktif jika opsi CLI kosong
        $name = $this->option('name') ?: $this->ask('Masukkan Nama Lengkap');
        $email = $this->option('email') ?: $this->ask('Masukkan Email Aktif');
        $username = $this->option('username') ?: $this->ask('Masukkan Username (opsional, tekan Enter untuk sama dengan email)');
        if (empty($username)) {
            $username = $email;
        }

        $role = $this->option('role');
        if (!in_array($role, ['admin', 'tu'], true)) {
            $role = $this->choice('Pilih Role Pengguna', ['admin', 'tu'], 0);
        }

        // Password wajib selalu dimasukkan secara interaktif dan terselubung (secret)
        $password = $this->secret('Masukkan Password (minimal 8 karakter)');
        $passwordConfirmation = $this->secret('Konfirmasi Password');

        if ($password !== $passwordConfirmation) {
            $this->error('Validasi Gagal: Password dan Konfirmasi Password tidak cocok.');
            return self::FAILURE;
        }

        // 2. Validasi input
        $validator = Validator::make([
            'name'     => $name,
            'email'    => $email,
            'username' => $username,
            'role'     => $role,
            'password' => $password,
        ], [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'username' => 'required|string|max:255|unique:users,username',
            'role'     => 'required|in:admin,tu',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $this->error('Gagal membuat akun:');
            foreach ($validator->errors()->all() as $error) {
                $this->line(" - $error");
            }
            return self::FAILURE;
        }

        // 3. Simpan akun dengan password ter-hash
        $user = User::create([
            'name'              => $name,
            'email'             => $email,
            'username'          => $username,
            'password'          => Hash::make($password),
            'role'              => $role,
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $this->newLine();
        $this->info("SUKSES: Akun {$role} berhasil dibuat!");
        $this->table(
            ['ID', 'Nama', 'Email', 'Username', 'Role', 'Status'],
            [[$user->id, $user->name, $user->email, $user->username, $user->role, 'ACTIVE']]
        );

        return self::SUCCESS;
    }
}
