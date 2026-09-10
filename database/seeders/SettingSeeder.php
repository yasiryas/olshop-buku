<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'shipping_methods' => [
                ['code' => 'jne-reguler', 'courier' => 'JNE Reguler', 'cost' => 12000, 'eta' => '2-3 hari'],
                ['code' => 'jnt-express', 'courier' => 'J&T Express', 'cost' => 11000, 'eta' => '1-2 hari'],
                ['code' => 'gosend-now', 'courier' => 'GoSend Same Day', 'cost' => 25000, 'eta' => 'Sama hari'],
            ],
            'payment_methods' => [
                ['code' => 'bca', 'name' => 'Transfer Bank BCA', 'acc_number' => '12345678', 'acc_name' => 'Wigati Buku', 'active' => true],
                ['code' => 'bri', 'name' => 'Transfer Bank BRI', 'acc_number' => '00001234567', 'acc_name' => 'Wigati Buku', 'active' => true],
                ['code' => 'mandiri', 'name' => 'Transfer Bank Mandiri', 'acc_number' => '123000456789', 'acc_name' => 'Wigati Buku', 'active' => false],
            ],
            'wa_contact' => '6285713878266',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        \App\Support\StoreSettings::invalidateCache();
    }
}