<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Package;

class CustomerPackageService
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * @param  array<int, array{package_id: int, levels_count: int, discount_id?: int|null}>  $assignments
     */
    public function createPackageAssignments(Customer $customer, array $assignments, ?int $userId): void
    {
        if ($assignments === []) {
            return;
        }

        $packages = Package::query()
            ->where('status', 'active')
            ->whereIn('id', collect($assignments)->pluck('package_id')->unique())
            ->get()
            ->keyBy('id');

        foreach ($assignments as $assignment) {
            $package = $packages->get($assignment['package_id']);

            if (! $package) {
                continue;
            }

            $this->createPackageAssignment($customer, $package, $assignment['levels_count'], $userId, $assignment['discount_id'] ?? null);
        }
    }

    public function createPackageAssignment(Customer $customer, Package $package, int $levelsCount, ?int $userId, ?int $discountId = null): void
    {
        $price = round($package->level_price * $levelsCount, 2);
        
        $discountAmount = 0;
        if ($discountId) {
            $discount = \App\Models\Discount::find($discountId);
            if ($discount) {
                if ($discount->type === 'percentage') {
                    $discountAmount = round($price * ($discount->amount / 100), 2);
                } else {
                    $discountAmount = round($discount->amount, 2);
                }
                
                // Ensure discount doesn't exceed price
                if ($discountAmount > $price) {
                    $discountAmount = $price;
                }
            }
        }
        
        $finalPrice = $price - $discountAmount;

        $customerPackage = $customer->customerPackages()->create([
            'package_id' => $package->id,
            'discount_id' => $discountId,
            'levels_count' => $levelsCount,
            'price' => $price,
            'discount' => $discountAmount,
            'final_price' => $finalPrice,
            'paid_amount' => 0,
            'remaining_amount' => $finalPrice,
            'payment_date' => null,
            'payment_status' => 'unpaid',
            'start_date' => now()->toDateString(),
            'end_date' => null,
            'status' => 'active',
            'created_by' => $userId,
        ]);

        $this->paymentService->applyWalletBalanceToNewSubscription($customer, $customerPackage, $userId);

        if (in_array($customer->status, [
            \App\Enums\CustomerStatus::New,
            \App\Enums\CustomerStatus::Inactive,
            \App\Enums\CustomerStatus::Finished,
            \App\Enums\CustomerStatus::Paused,
        ])) {
            $customer->update([
                'status' => \App\Enums\CustomerStatus::Waiting,
                'status_changed_at' => now(),
            ]);
        }
    }
}
