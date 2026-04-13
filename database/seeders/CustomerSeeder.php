<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->first();

        Customer::factory()
            ->count(30)
            ->state(fn () => [
                'created_by' => $admin?->id,
            ])
            ->create();
    }
}
