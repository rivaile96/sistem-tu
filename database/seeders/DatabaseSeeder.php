<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\SppBill;
use App\Models\PosItem;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Bikin User Login (Admin)
        User::factory()->create([
            'name' => 'Admin TU',
            'email' => 'admin@sekolah.com',
            'password' => bcrypt('password'),
        ]);

        // 2. Bikin Siswa
        $student = Student::create([
            'nis' => '1001',
            'name' => 'Kelvino',
            'class_name' => 'XII-RPL'
        ]);

        // 3. Bikin Tagihan SPP
        SppBill::create([
            'student_id' => $student->id,
            'month' => 'Februari 2024',
            'amount' => 350000,
            'status' => 'LUNAS',
            'payment_method' => 'Transfer',
            'paid_at' => now(),
        ]);
        
        SppBill::create([
            'student_id' => $student->id,
            'month' => 'Maret 2024',
            'amount' => 350000,
            'status' => 'BELUM',
        ]);

        // 4. Bikin Barang POS
        PosItem::create([
            'name' => 'Dasi Sekolah', 
            'category' => 'Atribut', 
            'price' => 15000, 
            'stock' => 5 // Stok dikit biar muncul alert
        ]);
    }
}