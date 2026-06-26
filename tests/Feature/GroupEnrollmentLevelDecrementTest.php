<?php

namespace Tests\Feature;

use App\Enums\GroupEnrollmentStatus;
use App\Enums\GroupStatus;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Group;
use App\Models\Package;
use App\Services\GroupEnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GroupEnrollmentLevelDecrementTest extends TestCase
{
    use RefreshDatabase;

    private GroupEnrollmentService $groupEnrollmentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->groupEnrollmentService = $this->app->make(GroupEnrollmentService::class);
    }

    #[Test]
    public function levels_count_decrements_when_group_becomes_active(): void
    {
        // 1. Create a customer
        $customer = Customer::query()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+20 1234567890',
            'customer_type' => 'new',
        ]);

        // 2. Create a package template
        $packageTemplate = Package::query()->create([
            'name' => 'General English',
            'levels_count' => 12,
            'price' => 1200.00,
            'status' => 'active',
        ]);

        // 3. Create a Customer Package subscription with 3 levels
        $customerPackage = CustomerPackage::query()->create([
            'customer_id' => $customer->id,
            'package_id' => $packageTemplate->id,
            'levels_count' => 3,
            'price' => 300.00,
            'final_price' => 300.00,
            'paid_amount' => 300.00,
            'remaining_amount' => 0.00,
            'start_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        // 4. Create a group that is Open (planned)
        $group = Group::query()->create([
            'name' => 'English Group A',
            'status' => GroupStatus::Open->value,
        ]);

        // 5. Enroll customer in the group with status 'ready'
        $enrollment = $group->groupEnrollments()->create([
            'customer_id' => $customer->id,
            'customer_package_id' => $customerPackage->id,
            'status' => GroupEnrollmentStatus::Ready->value,
        ]);

        // 6. Activate ready enrollments (simulate group starting/becoming active)
        $this->groupEnrollmentService->activateReadyEnrollments($group);

        // 7. Verify enrollment became Active
        $this->assertSame(GroupEnrollmentStatus::Active->value, $enrollment->fresh()->status);

        // 8. Verify levels count decremented from 3 to 2
        $this->assertSame(2, $customerPackage->fresh()->levels_count);
        $this->assertSame('active', $customerPackage->fresh()->status);
    }

    #[Test]
    public function levels_count_decrements_to_zero_completes_customer_package(): void
    {
        // 1. Create a customer
        $customer = Customer::query()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'phone' => '+20 9876543210',
            'customer_type' => 'new',
        ]);

        // 2. Create a package template
        $packageTemplate = Package::query()->create([
            'name' => 'Conversational French',
            'levels_count' => 6,
            'price' => 600.00,
            'status' => 'active',
        ]);

        // 3. Create a Customer Package subscription with 1 level
        $customerPackage = CustomerPackage::query()->create([
            'customer_id' => $customer->id,
            'package_id' => $packageTemplate->id,
            'levels_count' => 1,
            'price' => 100.00,
            'final_price' => 100.00,
            'paid_amount' => 100.00,
            'remaining_amount' => 0.00,
            'start_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        // 4. Create an Open group
        $group = Group::query()->create([
            'name' => 'French Group B',
            'status' => GroupStatus::Open->value,
        ]);

        // 5. Enroll customer with status 'ready'
        $enrollment = $group->groupEnrollments()->create([
            'customer_id' => $customer->id,
            'customer_package_id' => $customerPackage->id,
            'status' => GroupEnrollmentStatus::Ready->value,
        ]);

        // 6. Activate ready enrollments
        $this->groupEnrollmentService->activateReadyEnrollments($group);

        // 7. Verify enrollment became Active
        $this->assertSame(GroupEnrollmentStatus::Active->value, $enrollment->fresh()->status);

        // 8. Verify levels count decremented from 1 to 0 and status set to completed
        $updatedPackage = $customerPackage->fresh();
        $this->assertSame(0, $updatedPackage->levels_count);
        $this->assertSame('completed', $updatedPackage->status);
        $this->assertSame(now()->toDateString(), $updatedPackage->end_date->toDateString());
    }

    #[Test]
    public function levels_count_decrements_when_enrolled_directly_to_active_group(): void
    {
        // 1. Create a customer
        $customer = Customer::query()->create([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'phone' => '+20 1112223334',
            'customer_type' => 'new',
        ]);

        // 2. Create a package template
        $packageTemplate = Package::query()->create([
            'name' => 'IELTS Prep',
            'levels_count' => 4,
            'price' => 800.00,
            'status' => 'active',
        ]);

        // 3. Create an active Customer Package with 2 levels
        $customerPackage = CustomerPackage::query()->create([
            'customer_id' => $customer->id,
            'package_id' => $packageTemplate->id,
            'levels_count' => 2,
            'price' => 400.00,
            'final_price' => 400.00,
            'paid_amount' => 400.00,
            'remaining_amount' => 0.00,
            'start_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        // 4. Create an already Active group
        $group = Group::query()->create([
            'name' => 'Active English Group C',
            'status' => GroupStatus::Active->value,
        ]);

        // 5. Enroll customer directly to the active group
        $result = $this->groupEnrollmentService->enrollCustomerIds($group, collect([$customer->id]));

        // 6. Verify enrollment succeeded
        $this->assertSame(1, $result['added']);

        // 7. Verify the enrollment status is immediately Active
        $enrollment = $group->groupEnrollments()->where('customer_id', $customer->id)->first();
        $this->assertNotNull($enrollment);
        $this->assertSame(GroupEnrollmentStatus::Active->value, $enrollment->status);

        // 8. Verify customer package levels decremented from 2 to 1
        $this->assertSame(1, $customerPackage->fresh()->levels_count);
        $this->assertSame('active', $customerPackage->fresh()->status);
    }

    #[Test]
    public function cannot_enroll_customer_if_package_has_no_remaining_levels(): void
    {
        // 1. Create a customer with a subscription of 0 levels (status 'active')
        $customer1 = Customer::query()->create([
            'first_name' => 'Zero',
            'last_name' => 'Active',
            'phone' => '+20 1234567801',
            'customer_type' => 'new',
        ]);
        $packageTemplate = Package::query()->create([
            'name' => 'Test Package',
            'levels_count' => 4,
            'price' => 800.00,
            'status' => 'active',
        ]);
        CustomerPackage::query()->create([
            'customer_id' => $customer1->id,
            'package_id' => $packageTemplate->id,
            'levels_count' => 0,
            'price' => 400.00,
            'final_price' => 400.00,
            'paid_amount' => 400.00,
            'remaining_amount' => 0.00,
            'start_date' => now()->toDateString(),
            'status' => 'active',
        ]);

        // 2. Create another customer with a completed subscription (levels_count = 0, status 'completed')
        $customer2 = Customer::query()->create([
            'first_name' => 'Zero',
            'last_name' => 'Completed',
            'phone' => '+20 1234567802',
            'customer_type' => 'new',
        ]);
        CustomerPackage::query()->create([
            'customer_id' => $customer2->id,
            'package_id' => $packageTemplate->id,
            'levels_count' => 0,
            'price' => 400.00,
            'final_price' => 400.00,
            'paid_amount' => 400.00,
            'remaining_amount' => 0.00,
            'start_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $group = Group::query()->create([
            'name' => 'English Group D',
            'status' => GroupStatus::Open->value,
        ]);

        // 3. Verify availableCustomers does not return either customer
        $available = $this->groupEnrollmentService->availableCustomers($group);
        $this->assertFalse($available->contains('id', $customer1->id));
        $this->assertFalse($available->contains('id', $customer2->id));

        // 4. Try enrolling them and verify they are skipped
        $result = $this->groupEnrollmentService->enrollCustomerIds($group, collect([$customer1->id, $customer2->id]));
        $this->assertSame(0, $result['added']);
        $this->assertSame(2, $result['skipped']);
    }
}
