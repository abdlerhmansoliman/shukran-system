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
            $profile = $customer->profiles()->create($request->profileData());

            $this->customerPackageService->createPackageAssignments($profile, $request->packageAssignments(), $request->user()?->id);

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
            'customerPackages' => fn ($query) => $query->with('package')->latest()->take(3),
            'customerPackages.groupEnrollments',
        ]);

        $profileId = request('profile_id');
        $profile = $profileId
            ? $customer->profiles()->find($profileId)
            : $customer->profiles()->first();

        if ($profile) {
            $customer->forceFill($profile->only([
                'first_name', 'last_name',
                'age', 'gender', 'entry_level_id', 'current_level_id', 'category_id',
                'tester_id', 'placement_month', 'job', 'college', 'progress_report_link',
                'test_date', 'agreed_package_id', 'agreed_amount', 'keywords', 'notes',
            ]));
        }

        return view('customers.edit', [
            'customer' => $customer,
            ...$this->formData(),
        ]);
    }

    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        Gate::authorize('edit customers');

        $profileId = $request->query('profile_id') ?: $request->input('profile_id');

        DB::transaction(function () use ($request, $customer, $profileId) {
            $customerData = $request->customerData();

            // If updating a secondary profile, do not overwrite the customer's (parent's) first/last billing name.
            $firstProfile = $customer->profiles()->first();
            if ($profileId && $firstProfile && (int) $firstProfile->id !== (int) $profileId) {
                unset($customerData['first_name'], $customerData['last_name']);
            }

            $customer->update($customerData);

            $profile = $profileId
                ? $customer->profiles()->find($profileId)
                : $customer->profiles()->first();

            if ($profile) {
                $profile->update($request->profileData());
            } else {
                $profile = $customer->profiles()->create($request->profileData());
            }

            $this->customerPackageService->createPackageAssignments($profile, $request->packageAssignments(), $request->user()?->id);
        });

        return redirect()
            ->route('customers.show', [$customer, 'profile_id' => $profileId])
            ->with('success', __('Customer updated successfully.'));
    }

    public function show(Customer $customer)
    {
        Gate::authorize('view customers');

        $profiles = $customer->profiles()->get();
        $activeProfile = $profiles->firstWhere('id', request('profile_id')) ?? $profiles->first();

        if ($activeProfile) {
            $activeProfile->load([
                'entryLevel',
                'currentLevel',
                'feedbacks.creator',
                'feedbacks.level',
                'category.parent',
                'tester',
                'customerPackages.package',
                'customerPackages.discountTemplate',
                'customerPackages.creator',
                'customerPackages.payments',
                'groupEnrollments' => fn ($query) => $query->latest('joined_at')->latest(),
                'groupEnrollments.group.instructor',
                'groupEnrollments.group.level',
                'groupEnrollments.customerPackage.package',
            ]);

            $customer->forceFill($activeProfile->only([
                'first_name', 'last_name',
                'age', 'gender', 'entry_level_id', 'current_level_id', 'category_id',
                'tester_id', 'placement_month', 'job', 'college', 'progress_report_link',
                'test_date', 'agreed_package_id', 'agreed_amount', 'keywords',
            ]));

            $customer->setRelation('entryLevel', $activeProfile->entryLevel);
            $customer->setRelation('currentLevel', $activeProfile->currentLevel);
            $customer->setRelation('category', $activeProfile->category);
            $customer->setRelation('tester', $activeProfile->tester);
            $customer->setRelation('agreedPackage', $activeProfile->agreedPackage);
            $customer->setRelation('customerPackages', $activeProfile->customerPackages);
            $customer->setRelation('groupEnrollments', $activeProfile->groupEnrollments);
            $customer->setRelation('feedbacks', $activeProfile->feedbacks);
        } else {
            $customer->setRelation('customerPackages', collect());
            $customer->setRelation('groupEnrollments', collect());
            $customer->setRelation('feedbacks', collect());
        }

        $customer->load([
            'country',
            'creator',
            'payments' => fn ($query) => $query->latest('paid_at')->latest(),
            'payments.creator',
            'payments.paymentMethod',
            'payments.customerPackage.package',
            'payments.payroll',
        ]);

        return view('customers.show', [
            'customer' => $customer,
            'profiles' => $profiles,
            'activeProfile' => $activeProfile,
            'levels' => Level::query()->orderBy('name')->get(),
            'availablePackages' => Package::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'availableDiscounts' => Discount::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'users' => User::query()->orderBy('name')->get(),
            'categories' => Category::query()->children()->with('parent')->orderBy('name')->get(),
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
