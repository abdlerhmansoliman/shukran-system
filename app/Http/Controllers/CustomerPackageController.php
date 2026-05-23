<?php

namespace App\Http\Controllers;

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

        $hasActiveEnrollment = $customerPackage->groupEnrollments()
            ->whereIn('status', GroupEnrollmentStatus::reservedValues())
            ->exists();

        if ($hasActiveEnrollment) {
            return back()->with('error', __('This subscription cannot be removed while it is reserved by a group enrollment.'));
        }

        $validated = $request->validate([
            'cancel_subscription_id' => ['nullable', 'integer'],
            'refund_amount' => ['nullable', 'numeric', 'min:0', 'max:'.(float) $customerPackage->paid_amount],
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

            $refundAmount = round((float) ($validated['refund_amount'] ?? 0), 2);

            if ($refundAmount > 0) {
                $this->paymentService->refundCancelledSubscriptionToWallet($lockedCustomer, $lockedCustomerPackage, $refundAmount, $request->user()?->id);
            }

            $lockedCustomerPackage->update([
                'status' => 'cancelled',
                'end_date' => now()->toDateString(),
            ]);
        });

        return back()->with('success', __('Subscription removed from customer successfully.'));
    }
}
