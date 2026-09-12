<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class IndonesiaCitySeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('data/kota-indonesia.json');

        if (!is_file($file)) {
            $this->command->error('File data kota tidak ditemukan: ' . $file);
            return;
        }

        $cities = collect(json_decode(file_get_contents($file), true))
            ->map(fn (array $city) => [
                'city_id' => (string) $city['id'],
                'city_name' => $city['city_name'],
                'province' => $city['province_name'],
                'postal_code' => (string) $city['postal_code'],
            ])
            ->sortBy('city_name')
            ->values()
            ->all();

        Setting::updateOrCreate(['key' => 'agenweb_city_list'], ['value' => $cities]);
        \App\Support\StoreSettings::invalidateCache();

        $this->command->info('Daftar kota Indonesia dimuat: ' . count($cities) . ' kota dari ' . $file);
    }
}