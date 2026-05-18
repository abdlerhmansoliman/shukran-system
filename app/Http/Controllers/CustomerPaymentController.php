<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerPaymentStoreRequest;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\PaymentMethod;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerPaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function create(Customer $customer)
    {
        $customer->load([
            'customerPackages' => fn ($query) => $query
                ->with('package')
                ->where('status', 'active')
                ->where('remaining_amount', '>', 0)
                ->latest(),
        ]);

        return view('customers.payments.create', [
            'customer' => $customer,
            'customerPackages' => $customer->customerPackages,
            'paymentMethods' => PaymentMethod::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(CustomerPaymentStoreRequest $request, Customer $customer)
    {
        DB::transaction(function () use ($request, $customer) {
            $customerPackage = CustomerPackage::query()
                ->where('customer_id', $customer->id)
                ->lockForUpdate()
                ->findOrFail($request->customerPackageId());

            if ((float) $request->validated('amount') > (float) $customerPackage->remaining_amount) {
                throw ValidationException::withMessages([
                    'amount' => __('The payment amount cannot be greater than the remaining subscription balance.'),
                ]);
            }

            $payment = $customer->payments()->create($request->paymentData());

            if ($payment->status === 'completed') {
                $this->paymentService->applyCustomerPackagePayment(
                    $customerPackage,
                    (float) $payment->amount,
                    $payment->paid_at?->toDateString()
                );
            }
        });

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', __('Customer payment recorded successfully.'));
    }
}
