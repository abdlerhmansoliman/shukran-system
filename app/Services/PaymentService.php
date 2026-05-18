<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Payment;

class PaymentService
{
    public function adjustWalletBalance(Customer $customer, float $amount): void
    {
        $walletBalance = max(round((float) $customer->wallet_balance + $amount, 2), 0);

        $customer->update([
            'wallet_balance' => $walletBalance,
        ]);
    }

    /**
     * @param  array<string, mixed>  $paymentData
     */
    public function applyTopUpToOutstandingBalances(Customer $customer, array $paymentData, ?int $userId): void
    {
        $remainingTopUpAmount = round((float) $paymentData['amount'], 2);

        $customerPackages = CustomerPackage::query()
            ->where('customer_id', $customer->id)
            ->where('status', 'active')
            ->where('remaining_amount', '>', 0)
            ->orderBy('start_date')
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();

        foreach ($customerPackages as $customerPackage) {
            if ($remainingTopUpAmount <= 0) {
                break;
            }

            $appliedAmount = min($remainingTopUpAmount, (float) $customerPackage->remaining_amount);

            if ($appliedAmount <= 0) {
                continue;
            }

            $customer->payments()->create([
                'customer_package_id' => $customerPackage->id,
                'amount' => $appliedAmount,
                'status' => $paymentData['status'],
                'payment_method_id' => $paymentData['payment_method_id'],
                'reference' => $paymentData['reference'],
                'paid_at' => $paymentData['paid_at'],
                'notes' => $paymentData['notes'],
                'direction' => $paymentData['direction'],
                'created_by' => $userId,
            ]);

            $this->applyCustomerPackagePayment(
                $customerPackage,
                $appliedAmount,
                $paymentData['paid_at']
            );

            $remainingTopUpAmount = round($remainingTopUpAmount - $appliedAmount, 2);
        }

        if ($remainingTopUpAmount <= 0) {
            return;
        }

        $customer->payments()->create([
            ...$paymentData,
            'amount' => $remainingTopUpAmount,
        ]);

        $this->adjustWalletBalance($customer, $remainingTopUpAmount);
    }

    public function applyWalletBalanceToNewSubscription(Customer $customer, CustomerPackage $customerPackage, ?int $userId): void
    {
        $availableWalletBalance = round((float) $customer->wallet_balance, 2);

        if ($availableWalletBalance <= 0) {
            return;
        }

        $amount = min($availableWalletBalance, (float) $customerPackage->remaining_amount);

        if ($amount <= 0) {
            return;
        }

        $customer->payments()->create([
            'customer_package_id' => $customerPackage->id,
            'amount' => $amount,
            'status' => 'completed',
            'payment_method_id' => null,
            'method' => Payment::METHOD_WALLET_BALANCE,
            'paid_at' => now()->toDateString(),
            'notes' => null,
            'direction' => 'incoming',
            'created_by' => $userId,
        ]);

        $this->adjustWalletBalance($customer, -$amount);
        $this->applyCustomerPackagePayment($customerPackage, $amount, now()->toDateString());
    }

    public function refundCancelledSubscriptionToWallet(Customer $customer, CustomerPackage $customerPackage, float $refundAmount, ?int $userId): void
    {
        if ($refundAmount <= 0) {
            return;
        }

        $customer->payments()->create([
            'customer_package_id' => $customerPackage->id,
            'amount' => $refundAmount,
            'status' => 'completed',
            'payment_method_id' => null,
            'method' => Payment::METHOD_WALLET_BALANCE,
            'paid_at' => now()->toDateString(),
            'notes' => __('Subscription refund was returned to the customer wallet.'),
            'direction' => 'outgoing',
            'created_by' => $userId,
        ]);

        $this->adjustWalletBalance($customer, $refundAmount);
    }

    public function applyCustomerPackagePayment(CustomerPackage $customerPackage, float $amount, ?string $paidAt): void
    {
        $finalPrice = (float) $customerPackage->final_price;
        $paidAmount = min(round((float) $customerPackage->paid_amount + $amount, 2), $finalPrice);
        $remainingAmount = max(round($finalPrice - $paidAmount, 2), 0);

        $customerPackage->update([
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
            'payment_date' => $paidAt ?? now()->toDateString(),
            'payment_status' => match (true) {
                $remainingAmount <= 0 => 'paid',
                $paidAmount > 0 => 'partial',
                default => 'unpaid',
            },
        ]);
    }
}
