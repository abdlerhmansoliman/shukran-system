<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            'Private',
            'Offline',
            'Placement Test',
        ];

        foreach ($programs as $program) {
            \App\Models\Program::firstOrCreate(['name' => $program]);
        }
    }
}
