<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $adults = Category::query()->firstOrCreate([
            'name' => 'Adults',
            'parent_id' => null,
        ]);

        $kids = Category::query()->firstOrCreate([
            'name' => 'Kids',
            'parent_id' => null,
        ]);

        Category::query()->firstOrCreate([
            'name' => '6-8',
            'parent_id' => $kids->id,
        ]);

        Category::query()->firstOrCreate([
            'name' => '9-12',
            'parent_id' => $kids->id,
        ]);

        Category::query()->firstOrCreate([
            'name' => '13-15',
            'parent_id' => $kids->id,
        ]);
    }
}
