<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerDataTable;
use App\Enums\GroupStatus;
use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Models\Category;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\Group;
use App\Models\Level;
use App\Models\Package;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\CustomerPackageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerPackageService $customerPackageService
    ) {}

    public function index(CustomerDataTable $datatable)
    {
        Gate::authorize('view customers');

        return $datatable->render('customers.index', [
            'groups' => Group::query()
                ->whereIn('status', [GroupStatus::Open->value, GroupStatus::Active->value])
                ->orderBy('name')
                ->get(),
            'levels' => Level::query()->orderBy('name')->get(),
            'categories' => Category::query()->children()->with('parent')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        Gate::authorize('create customers');

        return view('customers.create', $this->formData());
    }

    public function store(CustomerStoreRequest $request)
    {
        Gate::authorize('create customers');

        $customer = DB::transaction(function () use ($request) {
            $customer = Customer::query()->create($request->customerData());

            $this->customerPackageService->createPackageAssignments($customer, $request->packageAssignments(), $request->user()?->id);

            return $customer;
        });

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', __('Customer created successfully.'));
    }

    public function edit(Customer $customer)
    {
        Gate::authorize('edit customers');

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
        Gate::authorize('edit customers');

        DB::transaction(function () use ($request, $customer) {
            $customer->update($request->customerData());

            $this->customerPackageService->createPackageAssignments($customer, $request->packageAssignments(), $request->user()?->id);
        });

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', __('Customer updated successfully.'));
    }

    public function show(Customer $customer)
    {
        Gate::authorize('view customers');

        $customer->load([
            'entryLevel',
            'currentLevel',
            'feedbacks.creator',
            'feedbacks.level',
            'category.parent',
            'country',
            'creator',
            'tester',
            'customerPackages.package',
            'customerPackages.discountTemplate',
            'customerPackages.creator',
            'customerPackages.payments',
            'groupEnrollments' => fn ($query) => $query->latest('joined_at')->latest(),
            'groupEnrollments.group.instructor',
            'groupEnrollments.group.level',
            'groupEnrollments.customerPackage.package',
            'payments' => fn ($query) => $query->latest('paid_at')->latest(),
            'payments.creator',
            'payments.paymentMethod',
            'payments.customerPackage.package',
            'payments.payroll',
        ]);

        return view('customers.show', [
            'customer' => $customer,
            'levels' => Level::query()->orderBy('name')->get(),
            'availablePackages' => Package::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'availableDiscounts' => Discount::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ]);
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
            'discounts' => Discount::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'users' => User::query()
                ->orderBy('name')
                ->get(),
            'paymentMethods' => PaymentMethod::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ];
    }
}
