<?php

namespace App\Http\Controllers;

use App\Enums\CustomerStatus;
use App\Enums\GroupEnrollmentStatus;
use App\Http\Requests\CustomerPackageStoreRequest;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Services\CustomerPackageService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CustomerPackageController extends Controller
{
    public function __construct(
        private CustomerPackageService $customerPackageService,
        private PaymentService $paymentService
    ) {}

    public function store(CustomerPackageStoreRequest $request, Customer $customer)
    {
        Gate::authorize('edit customers');
        DB::transaction(function () use ($request, $customer) {
            $lockedCustomer = Customer::query()
                ->lockForUpdate()
                ->findOrFail($customer->id);

            $this->customerPackageService->createPackageAssignments($lockedCustomer, [[
                'package_id' => $request->packageId(),
                'levels_count' => $request->levelsCount(),
            ]], $request->user()?->id);
        });

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', __('Subscription added to customer successfully.'));
    }

    public function destroy(Request $request, Customer $customer, CustomerPackage $customerPackage)
    {
        Gate::authorize('edit customers');
        abort_unless((int) $customerPackage->customer_id === (int) $customer->id, 404);

        $validated = $request->validate([
            'cancel_subscription_id' => ['nullable', 'integer'],
            'refund_amount' => ['nullable', 'numeric', 'min:0', 'max:'.(float) $customerPackage->paid_amount],
            'refund_reason' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if ((float) $request->input('refund_amount') > 0 && blank($value)) {
                        $fail(__('A reason is required for the refund.'));
                    }
                },
            ],
        ], [
            'refund_amount.max' => __('The refund amount cannot be greater than the paid amount.'),
        ]);

        DB::transaction(function () use ($request, $customer, $customerPackage, $validated) {
            $lockedCustomer = Customer::query()
                ->lockForUpdate()
                ->findOrFail($customer->id);

            $lockedCustomerPackage = CustomerPackage::query()
                ->where('customer_id', $lockedCustomer->id)
                ->lockForUpdate()
                ->findOrFail($customerPackage->id);

            if ($lockedCustomerPackage->status === 'cancelled') {
                return;
            }

            // Cancel associated active/pending group enrollments
            $lockedCustomerPackage->groupEnrollments()
                ->whereIn('status', GroupEnrollmentStatus::reservedValues())
                ->update([
                    'status' => GroupEnrollmentStatus::Cancelled->value,
                    'left_at' => now()->toDateString(),
                ]);

            $refundAmount = round((float) ($validated['refund_amount'] ?? 0), 2);

            if ($refundAmount > 0) {
                $this->paymentService->refundCancelledSubscriptionToWallet(
                    $lockedCustomer,
                    $lockedCustomerPackage,
                    $refundAmount,
                    $request->user()?->id,
                    $validated['refund_reason'] ?? null
                );
            }

            $lockedCustomerPackage->update([
                'status' => 'cancelled',
                'end_date' => now()->toDateString(),
            ]);

            // If the customer has no other active subscriptions, mark them as Finished
            $hasOtherActiveSubscriptions = CustomerPackage::query()
                ->where('customer_id', $lockedCustomer->id)
                ->where('id', '!=', $lockedCustomerPackage->id)
                ->where('status', 'active')
                ->exists();

            if (! $hasOtherActiveSubscriptions) {
                $lockedCustomer->update([
                    'status' => CustomerStatus::Finished,
                    'status_changed_at' => now(),
                ]);
            }
        });

        return back()->with('success', __('Subscription removed from customer successfully.'));
    }
}
