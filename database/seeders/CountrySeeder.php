<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use RuntimeException;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/countries.json');
        $countries = json_decode(file_get_contents($path), true);

        if (! is_array($countries)) {
            throw new RuntimeException('The countries JSON file is invalid.');
        }

        $timestamp = now();

        Country::query()->upsert(
            array_map(
                static fn (array $country): array => [
                    'name' => $country['name'],
                    'code' => $country['alpha2'],
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
                $countries
            ),
            ['code'],
            ['name', 'updated_at']
        );
    }
}
