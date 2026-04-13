<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            'Beginner',
            'Intermediate',
            'Advanced',
        ];

        foreach ($levels as $level) {
            Level::create([
                'name' => $level,
            ]);
        }
    }
}