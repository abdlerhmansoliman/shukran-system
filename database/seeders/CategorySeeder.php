<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $adults = Category::create([
            'name' => 'Adults',
            'parent_id' => null,
        ]);

        $kids = Category::create([
            'name' => 'Kids',
            'parent_id' => null,
        ]);

        Category::create([
            'name' => '6-8',
            'parent_id' => $kids->id,
        ]);

        Category::create([
            'name' => '9-12',
            'parent_id' => $kids->id,
        ]);

        Category::create([
            'name' => '13-15',
            'parent_id' => $kids->id,
        ]);
    }
}