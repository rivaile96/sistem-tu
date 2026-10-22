<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed jenis tagihan default.
 *
 * Dijalankan SEKALI saat setup awal — aman dijalankan berulang
 * karena menggunakan updateOrInsert berdasarkan code.
 *
 * TIDAK mengubah student_bills atau data finance yang sudah ada.
 */
class FinanceBillingTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now   = now();
        $types = [
            [
                'code'         => 'SPP',
                'name'         => 'SPP Bulanan',
                'description'  => 'Sumbangan Pembinaan Pendidikan — tagihan bulanan rutin',
                'is_recurring' => true,
                'sort_order'   => 1,
                'is_active'    => true,
            ],
            [
                'code'         => 'GEDUNG',
                'name'         => 'Biaya Gedung',
                'description'  => 'Biaya pembangunan dan pemeliharaan gedung sekolah',
                'is_recurring' => false,
                'sort_order'   => 2,
                'is_active'    => true,
            ],
            [
                'code'         => 'PONDOK',
                'name'         => 'Biaya Pondok',
                'description'  => 'Biaya asrama / pondok pesantren',
                'is_recurring' => true,
                'sort_order'   => 3,
                'is_active'    => true,
            ],
            [
                'code'         => 'KEGIATAN',
                'name'         => 'Biaya Kegiatan',
                'description'  => 'Biaya kegiatan sekolah (study tour, wisuda, dll)',
                'is_recurring' => false,
                'sort_order'   => 4,
                'is_active'    => true,
            ],
            [
                'code'         => 'SERAGAM',
                'name'         => 'Biaya Seragam',
                'description'  => 'Pengadaan seragam sekolah',
                'is_recurring' => false,
                'sort_order'   => 5,
                'is_active'    => true,
            ],
            [
                'code'         => 'OTHER',
                'name'         => 'Lainnya',
                'description'  => 'Tagihan lain yang tidak termasuk kategori di atas',
                'is_recurring' => false,
                'sort_order'   => 99,
                'is_active'    => true,
            ],
        ];

        foreach ($types as $type) {
            DB::table('finance_billing_types')->updateOrInsert(
                ['code' => $type['code']],
                array_merge($type, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}
