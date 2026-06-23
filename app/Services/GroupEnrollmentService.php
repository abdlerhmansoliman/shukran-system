<?php

namespace App\Services;

use App\Enums\CustomerStatus;
use App\Enums\GroupEnrollmentStatus;
use App\Enums\GroupStatus;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Group;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GroupEnrollmentService
{
    /**
     * @return Collection<int, Customer>
     */
    public function availableCustomers(Group $group): Collection
    {
        return Customer::query()
            ->whereDoesntHave('groupEnrollments', fn ($query) => $query->where('group_id', $group->id))
            ->whereHas('customerPackages', function ($builder) {
                $builder
                    ->where('status', 'active')
                    ->whereDoesntHave('groupEnrollments', fn ($query) => $query->whereIn('status', GroupEnrollmentStatus::reservedValues()));
            })
            ->withExists(['groupEnrollments as has_rejected' => function ($query) {
                $query->where('status', GroupEnrollmentStatus::Rejected->value);
            }])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    /**
     * @param  Collection<int, mixed>  $customerIds
     * @return array{added: int, skipped: int}
     */
    public function enrollCustomerIds(Group $group, Collection $customerIds): array
    {
        return DB::transaction(function () use ($group, $customerIds) {
            $customerIds = $customerIds
                ->map(fn ($customerId) => (int) $customerId)
                ->unique()
                ->values();

            $existingCustomerIds = $group->groupEnrollments()
                ->whereIn('customer_id', $customerIds)
                ->pluck('customer_id');

            $newCustomerIds = $customerIds
                ->diff($existingCustomerIds)
                ->values();

            $capacitySkipped = 0;

            if ($group->capacity) {
                $activeCount = $group->groupEnrollments()
                    ->whereIn('status', GroupEnrollmentStatus::reservedValues())
                    ->count();
                $availableSlots = max($group->capacity - $activeCount, 0);

                if ($newCustomerIds->count() > $availableSlots) {
                    $capacitySkipped = $newCustomerIds->count() - $availableSlots;
                    $newCustomerIds = $newCustomerIds->take($availableSlots)->values();
                }
            }

            if ($newCustomerIds->isEmpty()) {
                return [
                    'added' => 0,
                    'skipped' => $existingCustomerIds->count() + $capacitySkipped,
                ];
            }

            $customerPackages = CustomerPackage::query()
                ->whereIn('customer_id', $newCustomerIds)
                ->where('status', 'active')
                ->whereDoesntHave('groupEnrollments', fn ($query) => $query->whereIn('status', GroupEnrollmentStatus::reservedValues()))
                ->latest('created_at')
                ->get()
                ->unique('customer_id')
                ->keyBy('customer_id');

            $missingSubscriptionCount = $newCustomerIds->diff($customerPackages->keys())->count();
            $newCustomerIds = $newCustomerIds
                ->filter(fn ($customerId) => $customerPackages->has($customerId))
                ->values();

            foreach ($newCustomerIds as $customerId) {
                $status = $this->newEnrollmentStatus($group);
                
                $group->groupEnrollments()->create([
                    'customer_id' => $customerId,
                    'customer_package_id' => $customerPackages->get($customerId)?->id,
                    'status' => $status,
                    'joined_at' => $status === GroupEnrollmentStatus::Active->value ? now()->toDateString() : null,
                ]);
            }

            if ($newCustomerIds->isNotEmpty()) {
                Customer::whereIn('id', $newCustomerIds)->update([
                    'status' => CustomerStatus::WaitingForAppointment->value
                ]);
            }

            return [
                'added' => $newCustomerIds->count(),
                'skipped' => $existingCustomerIds->count() + $capacitySkipped + $missingSubscriptionCount,
            ];
        });
    }

    public function activateReadyEnrollments(Group $group): void
    {
        $group->groupEnrollments()
            ->where('status', GroupEnrollmentStatus::Ready->value)
            ->update([
                'status' => GroupEnrollmentStatus::Active->value,
                'joined_at' => now()->toDateString(),
                'left_at' => null,
            ]);
    }

    private function newEnrollmentStatus(Group $group): string
    {
        return $group->status === GroupStatus::Active->value
            ? GroupEnrollmentStatus::Active->value
            : GroupEnrollmentStatus::Pending->value;
    }
}
