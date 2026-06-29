<?php

namespace App\Services;

use App\Enums\CustomerStatus;
use App\Models\Discount;
use App\Models\Package;
use App\Models\Profile;

class CustomerPackageService
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * @param  array<int, array{package_id: int, levels_count: int, discount_id?: int|null}>  $assignments
     */
    public function createPackageAssignments(Profile $profile, array $assignments, ?int $userId): void
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

            $this->createPackageAssignment($profile, $package, $assignment['levels_count'], $userId, $assignment['discount_id'] ?? null);
        }
    }

    public function createPackageAssignment(Profile $profile, Package $package, int $levelsCount, ?int $userId, ?int $discountId = null): void
    {
        $price = round($package->level_price * $levelsCount, 2);

        $discountAmount = 0;
        if ($discountId) {
            $discount = Discount::find($discountId);
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

        $customerPackage = $profile->customerPackages()->create([
            'customer_id' => $profile->customer_id,
            'profile_id' => $profile->id,
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

        $this->paymentService->applyWalletBalanceToNewSubscription($profile->customer, $customerPackage, $userId);

        if (in_array($profile->customer->status, [
            CustomerStatus::New,
            CustomerStatus::Inactive,
            CustomerStatus::Finished,
            CustomerStatus::Paused,
        ])) {
            $profile->customer->update([
                'status' => CustomerStatus::Waiting,
                'status_changed_at' => now(),
            ]);
        }
    }
}
