<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerPackageSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::query()->get();
        $packages = Package::query()->get();
        $adminId = User::query()->value('id');

        if ($customers->isEmpty() || $packages->isEmpty()) {
            return;
        }

        foreach ($customers as $customer) {
            if (fake()->boolean(65) === false) {
                continue;
            }

            $package = $packages->random();
            $price = (float) $package->price;
            $discount = fake()->randomElement([0, 50, 100, 150, 200]);
            $finalPrice = max($price - $discount, 0);
            $paidAmount = fake()->randomElement([
                0,
                round($finalPrice / 2, 2),
                $finalPrice,
            ]);
            $remainingAmount = max($finalPrice - $paidAmount, 0);
            $paymentStatus = match (true) {
                $paidAmount <= 0 => 'unpaid',
                $remainingAmount > 0 => 'partial',
                default => 'paid',
            };
            $startDate = fake()->dateTimeBetween('-3 months', 'now');
            $status = fake()->randomElement(['active', 'completed', 'cancelled']);
            $endDate = $status === 'active'
                ? null
                : fake()->dateTimeBetween($startDate, '+3 months');

            CustomerPackage::query()->updateOrCreate(
                [
                    'customer_id' => $customer->id,
                    'package_id' => $package->id,
                ],
                [
                    'price' => $price,
                    'discount' => $discount,
                    'final_price' => $finalPrice,
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $remainingAmount,
                    'payment_date' => $paidAmount > 0 ? fake()->dateTimeBetween($startDate, 'now') : null,
                    'payment_status' => $paymentStatus,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $status,
                    'created_by' => $adminId,
                ]
            );
        }
    }
}
