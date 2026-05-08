<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Bank', 'Instapay', 'Apple Pay'] as $name) {
            PaymentMethod::query()->updateOrCreate(
                ['name' => $name],
                ['status' => 'active']
            );
        }
    }
}
