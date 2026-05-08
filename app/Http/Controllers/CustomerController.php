<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerDataTable;
use App\Enums\CustomerStatus;
use App\Enums\GroupStatus;
use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Models\Category;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Group;
use App\Models\Level;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
        $customer = Customer::query()->create($request->customerData());
        $packageIds = collect($request->validated('package_ids', []));

        if ($packageIds->isNotEmpty()) {
            $packages = Package::query()
                ->whereIn('id', $packageIds)
                ->get();

            $customer->customerPackages()->createMany(
                $packages->map(fn (Package $package) => [
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
                    'created_by' => $request->user()?->id,
                ])->all()
            );
        }

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', __('Customer created successfully.'));
    }

    public function edit(Customer $customer)
    {
        $customer->load('customerPackages.package');

        return view('customers.edit', [
            'customer' => $customer,
            ...$this->formData(),
        ]);
    }

    public function update(CustomerUpdateRequest $request, Customer $customer)
    {
        DB::transaction(function () use ($request, $customer) {
            $customer->update($request->customerData());
            $this->syncPackageAssignment($request, $customer);
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
        ]);

        return view('customers.show', compact('customer'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'categories' => Category::query()
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
            'statuses' => CustomerStatus::options(),
            'users' => User::query()
                ->orderBy('name')
                ->get(),
        ];
    }

    private function syncPackageAssignment(CustomerUpdateRequest $request, Customer $customer): void
    {
        $validated = $request->validated();

        if (! array_key_exists('package_id', $validated)) {
            return;
        }

        $packageId = $validated['package_id'] ?? null;
        $activeAssignments = $customer->customerPackages()
            ->where('status', 'active')
            ->latest()
            ->get();
        $currentAssignment = $activeAssignments->first();

        if (! $packageId) {
            $customer->customerPackages()
                ->where('status', 'active')
                ->update([
                    'status' => 'cancelled',
                    'end_date' => now()->toDateString(),
                ]);

            return;
        }

        if ($currentAssignment && (int) $currentAssignment->package_id === (int) $packageId) {
            return;
        }

        $customer->customerPackages()
            ->where('status', 'active')
            ->update([
                'status' => 'completed',
                'end_date' => now()->toDateString(),
            ]);

        $package = Package::query()->findOrFail($packageId);

        $customer->customerPackages()->create([
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
            'created_by' => $request->user()?->id,
        ]);
    }
}
    private function syncPackageAssignment(CustomerUpdateRequest $request, Customer $customer): void
    {
        $validated = $request->validated();

        if (! array_key_exists('package_id', $validated)) {
            return;
        }

        $packageId = $validated['package_id'] ?? null;
        $activeAssignments = $customer->customerPackages()
            ->where('status', 'active')
            ->latest()
            ->get();
        $currentAssignment = $activeAssignments->first();

        if (! $packageId) {
            $customer->customerPackages()
                ->where('status', 'active')
                ->update([
                    'status' => 'cancelled',
                    'end_date' => now()->toDateString(),
                ]);

            return;
        }

        if ($currentAssignment && (int) $currentAssignment->package_id === (int) $packageId) {
            return;
        }

        $customer->customerPackages()
            ->where('status', 'active')
            ->update([
                'status' => 'completed',
                'end_date' => now()->toDateString(),
            ]);

        $package = Package::query()->findOrFail($packageId);

        $customer->customerPackages()->create([
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
            'created_by' => $request->user()?->id,
        ]);
    }