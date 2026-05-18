<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerWalletTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function completed_wallet_top_up_increases_the_customer_balance(): void
    {
        $user = User::factory()->create();
        $customer = $this->createCustomer(walletBalance: 50);
        $paymentMethod = PaymentMethod::query()->create([
            'name' => 'Cash',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('customers.wallet.top-ups.store', $customer), [
            'amount' => 25.50,
            'paid_at' => '2026-05-13',
            'status' => 'completed',
            'payment_method_id' => $paymentMethod->id,
            'reference' => 'TOP-UP-1',
            'notes' => 'Customer added funds.',
        ]);

        $response->assertRedirect(route('customers.show', $customer));

        $this->assertSame(75.50, (float) $customer->fresh()->wallet_balance);

        $payment = Payment::query()->first();

        $this->assertNotNull($payment);
        $this->assertSame('incoming', $payment->direction);
        $this->assertSame('completed', $payment->status);
        $this->assertSame($paymentMethod->id, $payment->payment_method_id);
        $this->assertNull($payment->customer_package_id);
    }

    #[Test]
    public function completed_wallet_top_up_pays_outstanding_subscription_balance_before_wallet(): void
    {
        $user = User::factory()->create();
        $customer = $this->createCustomer(walletBalance: 0);
        $paymentMethod = PaymentMethod::query()->create([
            'name' => 'Bank',
            'status' => 'active',
        ]);
        $customerPackage = $this->createCustomerPackage($customer, $user, finalPrice: 300, paidAmount: 0);

        $response = $this->actingAs($user)->post(route('customers.wallet.top-ups.store', $customer), [
            'amount' => 500,
            'paid_at' => '2026-05-13',
            'status' => 'completed',
            'payment_method_id' => $paymentMethod->id,
            'reference' => 'TOP-UP-2',
        ]);

        $response->assertRedirect(route('customers.show', $customer));

        $this->assertSame(200.00, (float) $customer->fresh()->wallet_balance);

        $customerPackage->refresh();

        $this->assertSame(300.00, (float) $customerPackage->paid_amount);
        $this->assertSame(0.00, (float) $customerPackage->remaining_amount);
        $this->assertSame('paid', $customerPackage->payment_status);

        $this->assertDatabaseCount('payments', 2);
        $this->assertSame(300.00, (float) Payment::query()->where('customer_package_id', $customerPackage->id)->first()->amount);
        $this->assertSame(200.00, (float) Payment::query()->whereNull('customer_package_id')->first()->amount);
    }

    #[Test]
    public function completed_wallet_top_up_can_be_fully_consumed_by_outstanding_subscription_balance(): void
    {
        $user = User::factory()->create();
        $customer = $this->createCustomer(walletBalance: 0);
        $paymentMethod = PaymentMethod::query()->create([
            'name' => 'Instapay',
            'status' => 'active',
        ]);
        $customerPackage = $this->createCustomerPackage($customer, $user, finalPrice: 700, paidAmount: 0);

        $response = $this->actingAs($user)->post(route('customers.wallet.top-ups.store', $customer), [
            'amount' => 500,
            'paid_at' => '2026-05-13',
            'status' => 'completed',
            'payment_method_id' => $paymentMethod->id,
            'reference' => 'TOP-UP-3',
        ]);

        $response->assertRedirect(route('customers.show', $customer));

        $this->assertSame(0.00, (float) $customer->fresh()->wallet_balance);

        $customerPackage->refresh();

        $this->assertSame(500.00, (float) $customerPackage->paid_amount);
        $this->assertSame(200.00, (float) $customerPackage->remaining_amount);
        $this->assertSame('partial', $customerPackage->payment_status);

        $this->assertDatabaseCount('payments', 1);
        $this->assertSame(500.00, (float) Payment::query()->first()->amount);
        $this->assertSame($customerPackage->id, Payment::query()->first()->customer_package_id);
    }

    private function createCustomer(float $walletBalance): Customer
    {
        return Customer::query()->create([
            'first_name' => 'Sara',
            'last_name' => 'Ahmed',
            'phone' => '+20 1000000000',
            'customer_type' => 'new',
            'wallet_balance' => $walletBalance,
        ]);
    }

    private function createCustomerPackage(Customer $customer, User $user, float $finalPrice, float $paidAmount): CustomerPackage
    {
        $package = Package::query()->create([
            'name' => 'Starter Course',
            'levels_count' => 8,
            'price' => $finalPrice,
            'status' => 'active',
        ]);

        return CustomerPackage::query()->create([
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'price' => $finalPrice,
            'discount' => 0,
            'final_price' => $finalPrice,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $finalPrice - $paidAmount,
            'payment_status' => $paidAmount > 0 ? 'partial' : 'unpaid',
            'start_date' => '2026-05-01',
            'status' => 'active',
            'created_by' => $user->id,
        ]);
    }
}
