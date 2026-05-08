<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Group;
use App\Models\Package;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPackageAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_multiple_packages_are_created_with_customer_inside_customer_history(): void
    {
        $admin = User::factory()->create();
        $starterPackage = Package::query()->create([
            'name' => 'Starter',
            'levels_count' => 1,
            'price' => 1000,
            'status' => 'active',
        ]);
        $advancedPackage = Package::query()->create([
            'name' => 'Advanced',
            'levels_count' => 3,
            'price' => 2500,
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('customers.store'), [
                'first_name' => 'Nour',
                'last_name' => 'Ahmed',
                'phone' => '+20 1000000011',
                'second_phone_number' => '+20 1000000022',
                'status' => 'active',
                'customer_type' => 'new',
                'package_assignments' => [
                    ['package_id' => $starterPackage->id, 'quantity' => 3],
                    ['package_id' => $advancedPackage->id, 'quantity' => 1],
                ],
            ]);

        $customer = Customer::query()->where('phone', '+20 1000000011')->firstOrFail();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customers.show', $customer));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'second_phone_number' => '+20 1000000022',
        ]);

        $this->assertDatabaseHas('customer_packages', [
            'customer_id' => $customer->id,
            'package_id' => $starterPackage->id,
            'final_price' => '1000.00',
            'remaining_amount' => '1000.00',
            'payment_status' => 'unpaid',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('customer_packages', [
            'customer_id' => $customer->id,
            'package_id' => $advancedPackage->id,
            'final_price' => '2500.00',
            'remaining_amount' => '2500.00',
            'payment_status' => 'unpaid',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->assertSame(3, $customer->customerPackages()->where('package_id', $starterPackage->id)->count());
        $this->assertSame(4, $customer->customerPackages()->count());
    }

    public function test_editing_customer_profile_adds_packages_without_replacing_existing_packages(): void
    {
        $admin = User::factory()->create();
        $oldPackage = Package::query()->create([
            'name' => 'Starter',
            'levels_count' => 1,
            'price' => 1000,
            'status' => 'active',
        ]);
        $newPackage = Package::query()->create([
            'name' => 'Advanced',
            'levels_count' => 3,
            'price' => 2500,
            'status' => 'active',
        ]);
        $customer = Customer::query()->create($this->customerPayload());
        $currentAssignment = $customer->customerPackages()->create([
            'package_id' => $oldPackage->id,
            'price' => 1000,
            'discount' => 0,
            'final_price' => 1000,
            'paid_amount' => 0,
            'remaining_amount' => 1000,
            'payment_status' => 'unpaid',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->put(route('customers.update', $customer), [
                ...$this->customerPayload(),
                'package_assignments' => [
                    ['package_id' => $newPackage->id, 'quantity' => 2],
                ],
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customers.show', $customer));

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'second_phone_number' => '+20 1000000022',
        ]);

        $this->assertDatabaseHas('customer_packages', [
            'id' => $currentAssignment->id,
            'package_id' => $oldPackage->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('customer_packages', [
            'customer_id' => $customer->id,
            'package_id' => $newPackage->id,
            'status' => 'active',
        ]);

        $this->assertSame(2, $customer->fresh()->customerPackages()
            ->where('package_id', $newPackage->id)
            ->where('status', 'active')
            ->count());
    }

    public function test_editing_customer_profile_keeps_multiple_active_packages(): void
    {
        $admin = User::factory()->create();
        $oldPackage = Package::query()->create([
            'name' => 'Starter',
            'levels_count' => 1,
            'price' => 1000,
            'status' => 'active',
        ]);
        $removedPackage = Package::query()->create([
            'name' => 'Old Intensive',
            'levels_count' => 2,
            'price' => 1800,
            'status' => 'active',
        ]);
        $customer = Customer::query()->create($this->customerPayload());
        $oldAssignment = $customer->customerPackages()->create([
            'package_id' => $oldPackage->id,
            'price' => 1000,
            'discount' => 0,
            'final_price' => 1000,
            'paid_amount' => 0,
            'remaining_amount' => 1000,
            'payment_status' => 'unpaid',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
        $removedAssignment = $customer->customerPackages()->create([
            'package_id' => $removedPackage->id,
            'price' => 1800,
            'discount' => 0,
            'final_price' => 1800,
            'paid_amount' => 0,
            'remaining_amount' => 1800,
            'payment_status' => 'unpaid',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->put(route('customers.update', $customer), [
                ...$this->customerPayload(),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customers.show', $customer));

        $this->assertDatabaseHas('customer_packages', [
            'id' => $oldAssignment->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('customer_packages', [
            'id' => $removedAssignment->id,
            'status' => 'active',
        ]);

        $this->assertSame(2, $customer->fresh()->customerPackages()->where('status', 'active')->count());
    }

    public function test_editing_customer_profile_does_not_clear_active_packages(): void
    {
        $admin = User::factory()->create();
        $package = Package::query()->create([
            'name' => 'Starter',
            'levels_count' => 1,
            'price' => 1000,
            'status' => 'active',
        ]);
        $customer = Customer::query()->create($this->customerPayload());
        $currentAssignment = $customer->customerPackages()->create([
            'package_id' => $package->id,
            'price' => 1000,
            'discount' => 0,
            'final_price' => 1000,
            'paid_amount' => 0,
            'remaining_amount' => 1000,
            'payment_status' => 'unpaid',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->put(route('customers.update', $customer), [
                ...$this->customerPayload(),
                'package_id' => null,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customers.show', $customer));

        $this->assertDatabaseHas('customer_packages', [
            'id' => $currentAssignment->id,
            'status' => 'active',
        ]);
    }

    public function test_editing_customer_profile_does_not_change_group_linked_package(): void
    {
        $admin = User::factory()->create();
        $oldPackage = Package::query()->create([
            'name' => 'Starter',
            'levels_count' => 1,
            'price' => 1000,
            'status' => 'active',
        ]);
        $newPackage = Package::query()->create([
            'name' => 'Advanced',
            'levels_count' => 3,
            'price' => 2500,
            'status' => 'active',
        ]);
        $customer = Customer::query()->create($this->customerPayload());
        $assignment = $customer->customerPackages()->create([
            'package_id' => $oldPackage->id,
            'price' => 1000,
            'discount' => 0,
            'final_price' => 1000,
            'paid_amount' => 0,
            'remaining_amount' => 1000,
            'payment_status' => 'unpaid',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
        $group = Group::query()->create([
            'name' => 'Saturday A',
            'package_id' => $oldPackage->id,
            'status' => 'active',
        ]);
        $group->groupEnrollments()->create([
            'customer_id' => $customer->id,
            'customer_package_id' => $assignment->id,
            'status' => 'active',
            'joined_at' => '2026-05-09',
        ]);

        $response = $this
            ->actingAs($admin)
            ->put(route('customers.update', $customer), [
                ...$this->customerPayload(),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customers.show', $customer));

        $this->assertDatabaseHas('customer_packages', [
            'id' => $assignment->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseMissing('customer_packages', [
            'customer_id' => $customer->id,
            'package_id' => $newPackage->id,
            'status' => 'active',
        ]);
    }

    public function test_same_package_can_be_added_to_customer_more_than_once(): void
    {
        $admin = User::factory()->create();
        $package = Package::query()->create([
            'name' => 'Starter',
            'levels_count' => 1,
            'price' => 1000,
            'status' => 'active',
        ]);
        $customer = Customer::query()->create($this->customerPayload());
        $customer->customerPackages()->create([
            'package_id' => $package->id,
            'price' => 1000,
            'discount' => 0,
            'final_price' => 1000,
            'paid_amount' => 0,
            'remaining_amount' => 1000,
            'payment_status' => 'unpaid',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('customers.packages.store', $customer), [
                'package_id' => $package->id,
                'quantity' => 2,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customers.show', $customer));

        $this->assertSame(3, $customer->fresh()->customerPackages()
            ->where('package_id', $package->id)
            ->where('status', 'active')
            ->count());
    }

    public function test_customer_payment_updates_package_balance(): void
    {
        $admin = User::factory()->create();
        $paymentMethod = PaymentMethod::query()->create([
            'name' => 'Bank',
            'status' => 'active',
        ]);
        $package = Package::query()->create([
            'name' => 'Starter',
            'levels_count' => 1,
            'price' => 1000,
            'status' => 'active',
        ]);
        $customer = Customer::query()->create($this->customerPayload());
        $assignment = $customer->customerPackages()->create([
            'package_id' => $package->id,
            'price' => 1000,
            'discount' => 0,
            'final_price' => 1000,
            'paid_amount' => 0,
            'remaining_amount' => 1000,
            'payment_status' => 'unpaid',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('customers.payments.store', $customer), [
                'customer_package_id' => $assignment->id,
                'amount' => 400,
                'paid_at' => '2026-05-08',
                'status' => 'completed',
                'payment_method_id' => $paymentMethod->id,
                'reference' => 'TXN-123',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customers.show', $customer));

        $this->assertDatabaseHas('payments', [
            'payable_type' => Customer::class,
            'payable_id' => $customer->id,
            'customer_package_id' => $assignment->id,
            'amount' => '400.00',
            'direction' => 'incoming',
            'status' => 'completed',
            'payment_method_id' => $paymentMethod->id,
            'reference' => 'TXN-123',
        ]);

        $this->assertDatabaseHas('customer_packages', [
            'id' => $assignment->id,
            'paid_amount' => '400.00',
            'remaining_amount' => '600.00',
            'payment_date' => '2026-05-08',
            'payment_status' => 'partial',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function customerPayload(): array
    {
        return [
            'first_name' => 'Nour',
            'last_name' => 'Ahmed',
            'email' => 'nour.customer@example.com',
            'phone' => '+20 1000000011',
            'second_phone_number' => '+20 1000000022',
            'status' => 'active',
            'customer_type' => 'new',
        ];
    }
}
