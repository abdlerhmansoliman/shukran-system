<?php

namespace App\Http\Controllers;

use App\DataTables\PackageDataTable;
use App\Enums\PackageStatus;
use App\Http\Requests\PackageStoreRequest;
use App\Http\Requests\PackageUpdateRequest;
use App\Models\Category;
use App\Models\Package;
use App\Models\Program;
use Illuminate\Support\Facades\Gate;

class PackageController extends Controller
{
    public function index(PackageDataTable $datatable)
    {
        Gate::authorize('view packages');

        return $datatable->render('packages.index');
    }

    public function create()
    {
        Gate::authorize('create packages');

        return view('packages.create', $this->formData());
    }

    public function store(PackageStoreRequest $request)
    {
        Gate::authorize('create packages');
        $package = Package::query()->create($request->packageData());

        return redirect()
            ->route('packages.edit', $package)
            ->with('success', __('Package created successfully.'));
    }

    public function edit(Package $package)
    {
        Gate::authorize('edit packages');

        return view('packages.edit', [
            'package' => $package,
            ...$this->formData(),
        ]);
    }

    public function update(PackageUpdateRequest $request, Package $package)
    {
        Gate::authorize('edit packages');
        $package->update($request->packageData());

        return redirect()
            ->route('packages.edit', $package)
            ->with('success', __('Package updated successfully.'));
    }

    public function destroy(Package $package)
    {
        Gate::authorize('delete packages');
        if ($package->customerPackages()->exists()) {
            return redirect()
                ->route('packages.index')
                ->with('error', __('Package cannot be deleted while it is assigned to customers.'));
        }

        $package->delete();

        return redirect()
            ->route('packages.index')
            ->with('success', __('Package deleted successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'statuses' => PackageStatus::options(),
            'programs' => Program::query()->orderBy('name')->get(),
            'categories' => Category::query()->children()->with('parent')->orderBy('name')->get(),
        ];
    }
}
