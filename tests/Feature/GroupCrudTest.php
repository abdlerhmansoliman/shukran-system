<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Group;
use App\Models\Level;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_can_be_created(): void
    {
        $admin = User::factory()->create();
        $instructor = User::factory()->create(['name' => 'Instructor One']);
        $level = Level::query()->create(['name' => 'Level 1']);
        $category = Category::query()->create(['name' => 'Kids']);
        $package = Package::query()->create([
            'name' => 'Starter',
            'levels_count' => 12,
            'price' => 1200,
            'status' => 'active',
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('groups.store'), [
                'name' => 'Saturday A',
                'level_id' => $level->id,
                'category_id' => $category->id,
                'package_id' => $package->id,
                'instructor_id' => $instructor->id,
                'capacity' => 8,
                'start_date' => '2026-05-10',
                'end_date' => '2026-08-10',
                'days_of_week' => ['saturday', 'monday'],
                'start_time' => '10:00',
                'end_time' => '12:00',
                'status' => 'planned',
            ]);

        $group = Group::query()->where('name', 'Saturday A')->firstOrFail();

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('groups.show', $group));

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'Saturday A',
            'level_id' => $level->id,
            'category_id' => $category->id,
            'package_id' => $package->id,
            'instructor_id' => $instructor->id,
            'capacity' => 8,
            'status' => 'planned',
        ]);
    }

    public function test_customers_can_be_bulk_added_to_group_from_customer_table(): void
    {
        $admin = User::factory()->create();
        $package = Package::query()->create([
            'name' => 'Starter',
            'levels_count' => 12,
            'price' => 1200,
            'status' => 'active',
        ]);
        $group = Group::query()->create([
            'name' => 'Saturday A',
            'package_id' => $package->id,
            'capacity' => 5,
            'status' => 'active',
        ]);
        $firstCustomer = Customer::factory()->create(['status' => 'active']);
        $secondCustomer = Customer::factory()->create(['status' => 'active']);
        $assignment = $firstCustomer->customerPackages()->create([
            'package_id' => $package->id,
            'price' => 1200,
            'discount' => 0,
            'final_price' => 1200,
            'paid_amount' => 0,
            'remaining_amount' => 1200,
            'payment_status' => 'unpaid',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
        $secondAssignment = $secondCustomer->customerPackages()->create([
            'package_id' => $package->id,
            'price' => 1200,
            'discount' => 0,
            'final_price' => 1200,
            'paid_amount' => 0,
            'remaining_amount' => 1200,
            'payment_status' => 'unpaid',
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('customers.group-enrollments.store'), [
                'group_id' => $group->id,
                'customer_ids' => [$firstCustomer->id, $secondCustomer->id],
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('group_enrollments', [
            'group_id' => $group->id,
            'customer_id' => $firstCustomer->id,
            'customer_package_id' => $assignment->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('group_enrollments', [
            'group_id' => $group->id,
            'customer_id' => $secondCustomer->id,
            'customer_package_id' => $secondAssignment->id,
            'status' => 'active',
        ]);
    }

    public function test_customer_without_group_package_cannot_be_added_to_packaged_group(): void
    {
        $admin = User::factory()->create();
        $package = Package::query()->create([
            'name' => 'Starter',
            'levels_count' => 12,
            'price' => 1200,
            'status' => 'active',
        ]);
        $group = Group::query()->create([
            'name' => 'Saturday A',
            'package_id' => $package->id,
            'status' => 'active',
        ]);
        $customer = Customer::factory()->create(['status' => 'active']);

        $response = $this
            ->actingAs($admin)
            ->from(route('customers.index'))
            ->post(route('customers.group-enrollments.store'), [
                'group_id' => $group->id,
                'customer_ids' => [$customer->id],
            ]);

        $response
            ->assertRedirect(route('customers.index'))
            ->assertSessionHasErrors('customer_ids');

        $this->assertDatabaseMissing('group_enrollments', [
            'group_id' => $group->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_customers_can_be_added_from_group_profile(): void
    {
        $admin = User::factory()->create();
        $group = Group::query()->create([
            'name' => 'Saturday A',
            'status' => 'planned',
        ]);
        $customer = Customer::factory()->create(['status' => 'active']);

        $response = $this
            ->actingAs($admin)
            ->post(route('groups.customers.store', $group), [
                'customer_ids' => [$customer->id],
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('groups.show', $group));

        $this->assertDatabaseHas('group_enrollments', [
            'group_id' => $group->id,
            'customer_id' => $customer->id,
            'status' => 'active',
        ]);
    }

    public function test_inactive_customers_cannot_be_bulk_added_to_group_from_customer_table(): void
    {
        $admin = User::factory()->create();
        $group = Group::query()->create([
            'name' => 'Saturday A',
            'status' => 'active',
        ]);
        $inactiveCustomer = Customer::factory()->create(['status' => 'inactive']);

        $response = $this
            ->actingAs($admin)
            ->from(route('customers.index'))
            ->post(route('customers.group-enrollments.store'), [
                'group_id' => $group->id,
                'customer_ids' => [$inactiveCustomer->id],
            ]);

        $response
            ->assertRedirect(route('customers.index'))
            ->assertSessionHasErrors('customer_ids.0');

        $this->assertDatabaseMissing('group_enrollments', [
            'group_id' => $group->id,
            'customer_id' => $inactiveCustomer->id,
        ]);
    }

    public function test_inactive_customers_cannot_be_added_from_group_profile(): void
    {
        $admin = User::factory()->create();
        $group = Group::query()->create([
            'name' => 'Saturday A',
            'status' => 'planned',
        ]);
        $inactiveCustomer = Customer::factory()->create(['status' => 'inactive']);

        $response = $this
            ->actingAs($admin)
            ->from(route('groups.show', $group))
            ->post(route('groups.customers.store', $group), [
                'customer_ids' => [$inactiveCustomer->id],
            ]);

        $response
            ->assertRedirect(route('groups.show', $group))
            ->assertSessionHasErrors('customer_ids.0');

        $this->assertDatabaseMissing('group_enrollments', [
            'group_id' => $group->id,
            'customer_id' => $inactiveCustomer->id,
        ]);
    }
}
