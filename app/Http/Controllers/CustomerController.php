<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerDataTable;
use App\Enums\GroupEnrollmentStatus;
use App\Enums\GroupStatus;
use App\Http\Requests\CustomerPackageStoreRequest;
use App\Http\Requests\CustomerPaymentStoreRequest;
use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Http\Requests\CustomerWalletTopUpStoreRequest;
use App\Models\Category;
use App\Models\Country;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Group;
use App\Models\Level;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index(CustomerDataTable $datatable)
    {
        return $datatable->render('customers.index', [
            'groups' => Group::query()
                ->whereIn('status', [GroupStatus::Planned->value, GroupStatus::Active->value])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create()
    {
        return view('customers.create', $this->formData());
    }

    public function store(CustomerStoreRequest $request)
    {
        $customer = DB::transaction(function () use ($request) {
            $customer = Customer::query()->create($request->customerData());

            $this->createPackageAssignments($customer, $request->packageAssignments(), $request->user()?->id);

            return $customer;
        });

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', __('Customer created successfully.'));
    }

    public function edit(Customer $customer)
    {
        $customer->load([
            'customerPackages' => fn ($query) => $query->with('package')->latest(),
            'customerPackages.groupEnrollments',
        ]);

        return view('customers.edit', [
            'customer' => $customer,
            ...$this->formData(),
        ]);
    }

    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        DB::transaction(function () use ($request, $customer) {
            $customer->update($request->customerData());

            $this->createPackageAssignments($customer, $request->packageAssignments(), $request->user()?->id);
        });

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', __('Customer updated successfully.'));
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'level',
            'category.parent',
            'country',
            'creator',
            'tester',
            'oldInstructor',
            'customerPackages.package',
            'customerPackages.creator',
            'groupEnrollments' => fn ($query) => $query->latest('joined_at')->latest(),
            'groupEnrollments.group.instructor',
            'groupEnrollments.group.level',
            'groupEnrollments.group.category',
            'groupEnrollments.customerPackage.package',
            'payments' => fn ($query) => $query->latest('paid_at')->latest(),
            'payments.creator',
            'payments.paymentMethod',
            'payments.customerPackage.package',
            'payments.payroll',
        ]);

        return view('customers.show', [
            'customer' => $customer,
            'availablePackages' => Package::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function storePackage(CustomerPackageStoreRequest $request, Customer $customer)
    {
        DB::transaction(function () use ($request, $customer) {
            $lockedCustomer = Customer::query()
                ->lockForUpdate()
                ->findOrFail($customer->id);

            $this->createPackageAssignments($lockedCustomer, [[
                'package_id' => $request->packageId(),
                'quantity' => $request->quantity(),
            ]], $request->user()?->id);
        });

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', __('Subscription added to customer successfully.'));
    }

    public function destroySubscription(Request $request, Customer $customer, CustomerPackage $customerPackage)
    {
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
                $this->refundCancelledSubscriptionToWallet($lockedCustomer, $lockedCustomerPackage, $refundAmount, $request->user()?->id);
            }

            $lockedCustomerPackage->update([
                'status' => 'cancelled',
                'end_date' => now()->toDateString(),
            ]);
        });

        return back()->with('success', __('Subscription removed from customer successfully.'));
    }

    public function createPayment(Customer $customer)
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

    public function createWalletTopUp(Customer $customer)
    {
        return view('customers.wallets.top-up', [
            'customer' => $customer,
            'paymentMethods' => PaymentMethod::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function storeWalletTopUp(CustomerWalletTopUpStoreRequest $request, Customer $customer)
    {
        DB::transaction(function () use ($request, $customer) {
            $lockedCustomer = Customer::query()
                ->lockForUpdate()
                ->findOrFail($customer->id);

            $paymentData = $request->paymentData();

            if ($paymentData['status'] !== 'completed') {
                $lockedCustomer->payments()->create($paymentData);

                return;
            }

            $this->applyTopUpToOutstandingBalances(
                $lockedCustomer,
                $paymentData,
                $request->user()?->id
            );
        });

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', __('Wallet balance updated successfully.'));
    }

    public function storePayment(CustomerPaymentStoreRequest $request, Customer $customer)
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
                $this->applyCustomerPackagePayment(
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

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'categories' => Category::query()
                ->children()
                ->with('parent')
                ->orderBy('name')
                ->get(),
            'countries' => Country::query()
                ->orderBy('name')
                ->get(),
            'levels' => Level::query()
                ->orderBy('name')
                ->get(),
            'packages' => Package::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'users' => User::query()
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * @param  array<int, array{package_id: int, quantity: int}>  $assignments
     */
    private function createPackageAssignments(Customer $customer, array $assignments, ?int $userId): void
    {
        if ($assignments === []) {
            return;
        }

        $packages = Package::query()
            ->where('status', 'active')
            ->whereIn('id', collect($assignments)->pluck('package_id')->unique())
            ->get()
            ->keyBy('id');

        foreach ($assignments as $assignment) {
            $package = $packages->get($assignment['package_id']);

            if (! $package) {
                continue;
            }

            for ($count = 0; $count < $assignment['quantity']; $count++) {
                $this->createPackageAssignment($customer, $package, $userId);
            }
        }
    }

    private function createPackageAssignment(Customer $customer, Package $package, ?int $userId): void
    {
        $customerPackage = $customer->customerPackages()->create([
            'package_id' => $package->id,
            'price' => $package->price,
            'discount' => 0,
            'final_price' => $package->price,
            'paid_amount' => 0,
            'remaining_amount' => $package->price,
            'payment_date' => null,
            'payment_status' => 'unpaid',
            'start_date' => now()->toDateString(),
            'end_date' => null,
            'status' => 'active',
            'created_by' => $userId,
        ]);

        $this->applyWalletBalanceToNewSubscription($customer, $customerPackage, $userId);
    }

    private function adjustWalletBalance(Customer $customer, float $amount): void
    {
        $walletBalance = max(round((float) $customer->wallet_balance + $amount, 2), 0);

        $customer->update([
            'wallet_balance' => $walletBalance,
        ]);
    }

    /**
     * @param  array<string, mixed>  $paymentData
     */
    private function applyTopUpToOutstandingBalances(Customer $customer, array $paymentData, ?int $userId): void
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

    private function applyWalletBalanceToNewSubscription(Customer $customer, CustomerPackage $customerPackage, ?int $userId): void
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

    private function refundCancelledSubscriptionToWallet(Customer $customer, CustomerPackage $customerPackage, float $refundAmount, ?int $userId): void
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

    private function applyCustomerPackagePayment(CustomerPackage $customerPackage, float $amount, ?string $paidAt): void
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
