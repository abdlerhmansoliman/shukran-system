<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPackageAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_packages_are_created_with_customer_inside_customer_history(): void
    {
        $admin = User::factory()->create();
        $firstPackage = Package::query()->create([
            'name' => 'Starter',
            'levels_count' => 1,
            'price' => 1000,
            'status' => 'active',
        ]);
        $secondPackage = Package::query()->create([
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
                'package_ids' => [$firstPackage->id, $secondPackage->id],
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
            'package_id' => $firstPackage->id,
            'final_price' => '1000.00',
            'remaining_amount' => '1000.00',
            'payment_status' => 'unpaid',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('customer_packages', [
            'customer_id' => $customer->id,
            'package_id' => $secondPackage->id,
            'final_price' => '2500.00',
            'remaining_amount' => '2500.00',
            'payment_status' => 'unpaid',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
    }

    public function test_changing_customer_package_creates_new_history_row(): void
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
                'package_id' => $newPackage->id,
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
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('customer_packages', [
            'customer_id' => $customer->id,
            'package_id' => $newPackage->id,
            'final_price' => '2500.00',
            'remaining_amount' => '2500.00',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
    }

    public function test_clearing_customer_package_cancels_active_assignments(): void
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
            'status' => 'cancelled',
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
