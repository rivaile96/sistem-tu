<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentChannel;

class PaymentChannelSeeder extends Seeder
{
    public function run()
    {
        $channels = [
            // VIRTUAL ACCOUNT
            ['name' => 'BCA Virtual Account', 'code' => 'bca_va', 'type' => 'virtual_account', 'logo' => '/logos/bca.png', 'is_active' => true],
            ['name' => 'BSI Virtual Account', 'code' => 'bsi_va', 'type' => 'virtual_account', 'logo' => '/logos/bsi.png', 'is_active' => true],
            ['name' => 'Mandiri Virtual Account', 'code' => 'mandiri_va', 'type' => 'virtual_account', 'logo' => '/logos/mandiri.png', 'is_active' => true],
            ['name' => 'BNI Virtual Account', 'code' => 'bni_va', 'type' => 'virtual_account', 'logo' => '/logos/bni.png', 'is_active' => true],
            ['name' => 'BRI Virtual Account', 'code' => 'bri_va', 'type' => 'virtual_account', 'logo' => '/logos/bri.png', 'is_active' => true],
            ['name' => 'Permata Virtual Account', 'code' => 'permata_va', 'type' => 'virtual_account', 'logo' => '/logos/permata.png', 'is_active' => true],
            ['name' => 'CIMB Niaga Virtual Account', 'code' => 'cimb_va', 'type' => 'virtual_account', 'logo' => '/logos/cimb.png', 'is_active' => true],
            ['name' => 'Bank Lainnya', 'code' => 'other_va', 'type' => 'virtual_account', 'logo' => '/logos/atm_bersama.png', 'is_active' => true],
            
            // E-WALLET
            ['name' => 'GoPay', 'code' => 'gopay', 'type' => 'ewallet', 'logo' => '/logos/gopay.png', 'is_active' => true],
            ['name' => 'ShopeePay', 'code' => 'shopeepay', 'type' => 'ewallet', 'logo' => '/logos/shopeepay.png', 'is_active' => true],
            ['name' => 'OVO', 'code' => 'ovo', 'type' => 'ewallet', 'logo' => '/logos/ovo.png', 'is_active' => true],
            ['name' => 'DANA', 'code' => 'dana', 'type' => 'ewallet', 'logo' => '/logos/dana.png', 'is_active' => true],

            // CSTORE (Minimarket)
            ['name' => 'Indomaret', 'code' => 'indomaret', 'type' => 'cstore', 'logo' => '/logos/indomaret.png', 'is_active' => true],
            ['name' => 'Alfamart', 'code' => 'alfamart', 'type' => 'cstore', 'logo' => '/logos/alfamart.png', 'is_active' => true],

            // QRIS
            ['name' => 'QRIS (All Payment)', 'code' => 'qris', 'type' => 'qris', 'logo' => '/logos/qris.png', 'is_active' => true],
        ];

        foreach ($channels as $channel) {
            PaymentChannel::updateOrCreate(['code' => $channel['code']], $channel);
        }
    }
}