<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TariffSeeder extends Seeder
{
    public function run(): void
    {
        $tariffs = [
            [
                'vehicle_type' => 'motor',
                'hourly_rate' => 2000,
                'daily_max' => 20000,
                'grace_period_minutes' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vehicle_type' => 'mobil',
                'hourly_rate' => 5000,
                'daily_max' => 40000,
                'grace_period_minutes' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vehicle_type' => 'truk',
                'hourly_rate' => 10000,
                'daily_max' => 80000,
                'grace_period_minutes' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('tariffs')->upsert($tariffs, ['vehicle_type'], ['hourly_rate', 'daily_max', 'grace_period_minutes']);
    }
}
