<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PosItem;
use App\Models\Student;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin user
        User::firstOrCreate(
            ['email' => 'admin@sekolah.com'],
            [
                'name'              => 'Admin TU',
                'password'          => bcrypt('password'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 2. TU (Tata Usaha) user
        User::firstOrCreate(
            ['email' => 'tu@sekolah.com'],
            [
                'name'              => 'Staff TU',
                'password'          => bcrypt('password'),
                'role'              => 'tu',
                'email_verified_at' => now(),
            ]
        );

        // 3. Staf user
        User::firstOrCreate(
            ['email' => 'staf@sekolah.com'],
            [
                'name'              => 'Staff Sekolah',
                'password'          => bcrypt('password'),
                'role'              => 'staf',
                'email_verified_at' => now(),
            ]
        );

        // 4. Kepala Sekolah user
        User::firstOrCreate(
            ['email' => 'kepsek@sekolah.com'],
            [
                'name'              => 'Kepala Sekolah',
                'password'          => bcrypt('password'),
                'role'              => 'kepala_sekolah',
                'email_verified_at' => now(),
            ]
        );

        // 2. Demo student — class_name removed (Phase 9.3)
        Student::firstOrCreate(
            ['nis' => '1001'],
            ['name' => 'Kelvino']
        );

        // 3. Demo POS item
        PosItem::firstOrCreate(
            ['name' => 'Dasi Sekolah'],
            ['category' => 'Atribut', 'price' => 15000, 'stock' => 5]
        );

        // Note: SppBill seeder removed in Phase 6B-4.
        // Historical SPP data preserved in student_bills (type='SPP', spp_legacy_id set).
    }
}
