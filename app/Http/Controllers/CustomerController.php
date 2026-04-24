<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerDataTable;
use App\Enums\CustomerStatus;
use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Models\Category;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Level;
use App\Models\Package;
use App\Models\User;

class CustomerController extends Controller
{
    public function index(CustomerDataTable $datatable)
    {
        return $datatable->render('customers.index');
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
        $customer->update($request->customerData());

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
}
