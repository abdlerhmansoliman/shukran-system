<?php

namespace Tests\Feature;

use App\Console\Commands\AutoTransitionCustomerStatus;
use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerStatusTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function customer_status_enum_has_all_expected_cases(): void
    {
        $expected = ['new', 'active', 'inactive', 'waiting', 'waiting_for_appointment', 'finished', 'paused', 'dropped'];

        $this->assertSame($expected, CustomerStatus::values());
    }

    #[Test]
    public function customer_status_enum_returns_options(): void
    {
        $options = CustomerStatus::options();

        $this->assertIsArray($options);
        $this->assertCount(8, $options);
        $this->assertArrayHasKey('new', $options);
        $this->assertArrayHasKey('active', $options);
        $this->assertArrayHasKey('waiting_for_appointment', $options);
        $this->assertArrayHasKey('dropped', $options);
    }

    #[Test]
    public function customer_status_enum_returns_color_classes(): void
    {
        foreach (CustomerStatus::cases() as $case) {
            $this->assertNotEmpty($case->color(), "Color for {$case->value} should not be empty");
        }
    }

    #[Test]
    public function customer_status_enum_returns_labels(): void
    {
        foreach (CustomerStatus::cases() as $case) {
            $this->assertNotEmpty($case->label(), "Label for {$case->value} should not be empty");
        }
    }

    #[Test]
    public function new_customer_defaults_to_new_status(): void
    {
        $customer = Customer::query()->create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'phone' => '+20 1111111111',
            'customer_type' => 'new',
        ]);

        $this->assertSame(CustomerStatus::New, $customer->status);
    }

    #[Test]
    public function customer_status_can_be_set_to_any_valid_value(): void
    {
        $customer = Customer::query()->create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'phone' => '+20 1111111111',
            'customer_type' => 'new',
            'status' => CustomerStatus::Active,
        ]);

        $this->assertSame(CustomerStatus::Active, $customer->fresh()->status);
    }

    #[Test]
    public function auto_transition_moves_finished_customers_to_inactive_after_60_days(): void
    {
        $shouldTransition = $this->createCustomerWithStatus(CustomerStatus::Finished, daysAgo: 61);
        $shouldNotTransition = $this->createCustomerWithStatus(CustomerStatus::Finished, daysAgo: 30);

        $this->artisan(AutoTransitionCustomerStatus::class)->assertSuccessful();

        $this->assertSame(CustomerStatus::Inactive, $shouldTransition->fresh()->status);
        $this->assertSame(CustomerStatus::Finished, $shouldNotTransition->fresh()->status);
    }

    #[Test]
    public function auto_transition_moves_stale_non_protected_customers_to_inactive(): void
    {
        $staleNew = $this->createCustomerWithStatus(CustomerStatus::New, daysAgo: 61);
        $staleWaiting = $this->createCustomerWithStatus(CustomerStatus::Waiting, daysAgo: 61);

        $this->artisan(AutoTransitionCustomerStatus::class)->assertSuccessful();

        $this->assertSame(CustomerStatus::Inactive, $staleNew->fresh()->status);
        $this->assertSame(CustomerStatus::Inactive, $staleWaiting->fresh()->status);
    }

    #[Test]
    public function auto_transition_does_not_touch_active_paused_or_dropped_customers(): void
    {
        $activeCustomer = $this->createCustomerWithStatus(CustomerStatus::Active, daysAgo: 90);
        $pausedCustomer = $this->createCustomerWithStatus(CustomerStatus::Paused, daysAgo: 90);
        $droppedCustomer = $this->createCustomerWithStatus(CustomerStatus::Dropped, daysAgo: 90);

        $this->artisan(AutoTransitionCustomerStatus::class)->assertSuccessful();

        $this->assertSame(CustomerStatus::Active, $activeCustomer->fresh()->status);
        $this->assertSame(CustomerStatus::Paused, $pausedCustomer->fresh()->status);
        $this->assertSame(CustomerStatus::Dropped, $droppedCustomer->fresh()->status);
    }

    #[Test]
    public function auto_transition_does_not_touch_already_inactive_customers(): void
    {
        $inactiveCustomer = $this->createCustomerWithStatus(CustomerStatus::Inactive, daysAgo: 120);

        $this->artisan(AutoTransitionCustomerStatus::class)->assertSuccessful();

        $this->assertSame(CustomerStatus::Inactive, $inactiveCustomer->fresh()->status);
    }

    #[Test]
    public function customer_can_be_created_with_specific_status(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('customers.store'), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+20 1234567890',
            'customer_type' => 'new',
            'status' => CustomerStatus::Waiting->value,
        ]);

        $customer = Customer::query()->latest('id')->first();
        $response->assertRedirect(route('customers.show', $customer));

        $this->assertSame(CustomerStatus::Waiting, $customer->status);
    }

    #[Test]
    public function customer_status_can_be_updated(): void
    {
        $user = User::factory()->create();
        $customer = Customer::query()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+20 1234567890',
            'customer_type' => 'new',
            'status' => CustomerStatus::New,
        ]);

        $response = $this->actingAs($user)->put(route('customers.update', $customer), [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+20 1234567890',
            'customer_type' => 'new',
            'status' => CustomerStatus::Dropped->value,
        ]);

        $response->assertRedirect(route('customers.show', $customer));
        $this->assertSame(CustomerStatus::Dropped, $customer->fresh()->status);
    }

    private function createCustomerWithStatus(CustomerStatus $status, int $daysAgo): Customer
    {
        $pastDate = Carbon::now()->subDays($daysAgo);

        $customer = Customer::withoutEvents(function () use ($status) {
            return Customer::query()->create([
                'first_name' => 'Test',
                'last_name' => $status->value,
                'phone' => '+20 '.random_int(1000000000, 9999999999),
                'customer_type' => 'new',
                'status' => $status,
            ]);
        });

        DB::table('customers')
            ->where('id', $customer->id)
            ->update([
                'status_changed_at' => $pastDate,
                'created_at' => $pastDate,
                'updated_at' => $pastDate,
            ]);

        return $customer;
    }
}
