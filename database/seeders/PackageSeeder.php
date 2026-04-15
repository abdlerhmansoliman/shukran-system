<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Starter',
                'levels_count' => 12,
                'price' => 1200.00,
                'status' => 'active',
            ],
            [
                'name' => 'Growth',
                'levels_count' => 24,
                'price' => 2200.00,
                'status' => 'active',
            ],
            [
                'name' => 'Intensive',
                'levels_count' => 36,
                'price' => 3000.00,
                'status' => 'active',
            ],
        ];

        foreach ($packages as $package) {
            Package::query()->updateOrCreate(
                ['name' => $package['name']],
                $package
            );
        }
    }
}
