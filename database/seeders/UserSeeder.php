<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
            ],
            [
                'name' => 'Dania',
                'email' => 'dania@example.com',
            ],
            [
                'name' => 'Menna',
                'email' => 'menna@example.com',
            ],
            [
                'name' => 'Rowan',
                'email' => 'rowan@example.com',
            ],
            [
                'name' => 'Sara',
                'email' => 'sara@example.com',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate([
                'email' => $user['email'],
            ], [
                'name' => $user['name'],
                'password' => bcrypt('12345678'),
            ]);
        }
    }
}
