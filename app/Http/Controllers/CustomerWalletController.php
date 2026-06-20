<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerWalletTopUpStoreRequest;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CustomerWalletController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function create(Customer $customer)
    {
        Gate::authorize('edit customers');
        return view('customers.wallets.top-up', [
            'customer' => $customer,
            'paymentMethods' => PaymentMethod::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(CustomerWalletTopUpStoreRequest $request, Customer $customer)
    {
        Gate::authorize('edit customers');
        DB::transaction(function () use ($request, $customer) {
            $lockedCustomer = Customer::query()
                ->lockForUpdate()
                ->findOrFail($customer->id);

            $paymentData = $request->paymentData();

            if ($paymentData['status'] !== 'completed') {
                $lockedCustomer->payments()->create($paymentData);

                return;
            }

            $this->paymentService->applyTopUpToOutstandingBalances(
                $lockedCustomer,
                $paymentData,
                $request->user()?->id
            );
        });

        return redirect()
            ->route('customers.wallet.show', $customer)
            ->with('success', __('Wallet balance updated successfully.'));
    }
}
