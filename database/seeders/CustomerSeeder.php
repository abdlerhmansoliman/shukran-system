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
        $staffIds = User::query()->pluck('id');
        $existingCount = Customer::query()->count();
        $missingCount = max(30 - $existingCount, 0);

        if ($missingCount === 0) {
            return;
        }

        Customer::factory()
            ->count($missingCount)
            ->state(fn () => [
                'created_by' => $admin?->id,
                'tester_id' => $staffIds->isNotEmpty() ? $staffIds->random() : null,
            ])
            ->create();
    }
}
