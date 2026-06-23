<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultLogoUrl = rtrim((string) config('app.url', 'http://localhost'), '/').'/ic_segreens.png';

        Store::query()->updateOrCreate(
            ['name' => 'SEGreens Fresh Market'],
            [
                'description' => 'Fresh vegetables, fruits, herbs, and daily produce.',
                'address' => 'Jl. Kebun Sayur No. 1, Jakarta',
                'latitude' => -6.2000000,
                'longitude' => 106.8166667,
                'service_radius_km' => 10,
                'base_delivery_fee' => 10000,
                'phone_number' => '081234567891',
                'logo' => $defaultLogoUrl,
                'open_time' => '07:00:00',
                'close_time' => '21:00:00',
            ],
        );
    }
}
