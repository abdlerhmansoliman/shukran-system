<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerSubscriptionCancellationRefundTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function cancelling_a_subscription_can_return_a_custom_amount_to_wallet(): void
    {
        $user = User::factory()->create();
        $customer = Customer::query()->create([
            'first_name' => 'Nour',
            'last_name' => 'Hassan',
            'phone' => '+20 1000000002',
            'customer_type' => 'new',
            'wallet_balance' => 10,
        ]);

        $package = Package::query()->create([
            'name' => 'Speaking Course',
            'levels_count' => 5,
            'price' => 120,
            'status' => 'active',
        ]);

        $customerPackage = CustomerPackage::query()->create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'price' => 120,
            'discount' => 0,
            'final_price' => 120,
            'paid_amount' => 80,
            'remaining_amount' => 40,
            'payment_status' => 'partial',
            'start_date' => '2026-05-01',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('customers.subscriptions.destroy', [$customer, $customerPackage]), [
            'refund_amount' => 35,
            'refund_reason' => 'Change of mind',
        ]);

        $response->assertRedirect();

        $this->assertSame(45.00, (float) $customer->fresh()->wallet_balance);
        $this->assertSame('cancelled', $customerPackage->fresh()->status);

        $payment = Payment::query()->first();

        $this->assertNotNull($payment);
        $this->assertSame('outgoing', $payment->direction);
        $this->assertSame(Payment::METHOD_WALLET_BALANCE, $payment->method);
        $this->assertSame($customerPackage->id, $payment->customer_package_id);
        $this->assertSame(35.00, (float) $payment->amount);
        $this->assertStringContainsString('Change of mind', $payment->notes);
    }

    #[Test]
    public function refund_requires_a_reason_if_amount_is_greater_than_zero(): void
    {
        $user = User::factory()->create();
        $customer = Customer::query()->create([
            'first_name' => 'Nour',
            'last_name' => 'Hassan',
            'phone' => '+20 1000000002',
            'customer_type' => 'new',
            'wallet_balance' => 10,
        ]);

        $package = Package::query()->create([
            'name' => 'Speaking Course',
            'levels_count' => 5,
            'price' => 120,
            'status' => 'active',
        ]);

        $customerPackage = CustomerPackage::query()->create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'price' => 120,
            'discount' => 0,
            'final_price' => 120,
            'paid_amount' => 80,
            'remaining_amount' => 40,
            'payment_status' => 'partial',
            'start_date' => '2026-05-01',
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('customers.subscriptions.destroy', [$customer, $customerPackage]), [
            'refund_amount' => 35,
            'refund_reason' => '', // Blank
        ]);

        $response->assertSessionHasErrors('refund_reason');
    }
}
