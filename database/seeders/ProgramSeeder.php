<?php

namespace Database\Seeders;

use App\Models\Program;
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
            Program::firstOrCreate(['name' => $program]);
        }
    }
}
