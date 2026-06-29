<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerSubscriptionWalletTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function subscribing_to_a_package_automatically_uses_available_wallet_balance(): void
    {
        $user = User::factory()->create();
        $customer = Customer::query()->create([
            'first_name' => 'Mona',
            'last_name' => 'Ali',
            'phone' => '+20 1000000001',
            'customer_type' => 'new',
            'wallet_balance' => 120,
        ]);

        $package = Package::query()->create([
            'name' => 'Grammar Course',
            'levels_count' => 6,
            'level_price' => 100,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('customers.packages.store', $customer), [
            'package_id' => $package->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect(route('customers.show', $customer));

        $customer->refresh();
        $customerPackage = $customer->customerPackages()->first();

        $this->assertNotNull($customerPackage);
        $this->assertSame(20.00, (float) $customer->wallet_balance);
        $this->assertSame(100.00, (float) $customerPackage->paid_amount);
        $this->assertSame(0.00, (float) $customerPackage->remaining_amount);
        $this->assertSame('paid', $customerPackage->payment_status);

        $payment = Payment::query()->first();

        $this->assertNotNull($payment);
        $this->assertSame(Payment::METHOD_WALLET_BALANCE, $payment->method);
        $this->assertSame($customerPackage->id, $payment->customer_package_id);
        $this->assertSame('completed', $payment->status);
    }
}
