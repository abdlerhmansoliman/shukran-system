<?php

namespace App\Http\Controllers;

use App\DataTables\GroupDataTable;
use App\Enums\GroupEnrollmentStatus;
use App\Enums\GroupStatus;
use App\Http\Requests\CustomerBulkGroupEnrollmentRequest;
use App\Http\Requests\GroupCustomerStoreRequest;
use App\Http\Requests\GroupStoreRequest;
use App\Http\Requests\GroupUpdateRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerPackage;
use App\Models\Group;
use App\Models\Level;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    public function index(GroupDataTable $datatable)
    {
        return $datatable->render('groups.index');
    }

    public function create()
    {
        return view('groups.create', $this->formData());
    }

    public function store(GroupStoreRequest $request)
    {
        $group = Group::query()->create($request->groupData());

        return redirect()
            ->route('groups.show', $group)
            ->with('success', __('Group created successfully.'));
    }

    public function show(Group $group)
    {
        $group->load([
            'level',
            'category.parent',
            'package',
            'instructor',
            'groupEnrollments' => fn ($query) => $query->latest('joined_at')->latest(),
            'groupEnrollments.customer',
            'groupEnrollments.customerPackage.package',
        ]);

        return view('groups.show', [
            'group' => $group,
            'availableCustomers' => $this->availableCustomers($group),
            'canAddCustomers' => in_array($group->status, [GroupStatus::Planned->value, GroupStatus::Active->value], true),
        ]);
    }

    public function edit(Group $group)
    {
        return view('groups.edit', [
            'group' => $group,
            ...$this->formData(),
        ]);
    }

    public function update(GroupUpdateRequest $request, Group $group)
    {
        $group->update($request->groupData());

        return redirect()
            ->route('groups.show', $group)
            ->with('success', __('Group updated successfully.'));
    }

    public function destroy(Group $group)
    {
        if ($group->groupEnrollments()->exists()) {
            return redirect()
                ->route('groups.index')
                ->with('error', __('Group cannot be deleted while it has customers.'));
        }

        $group->delete();

        return redirect()
            ->route('groups.index')
            ->with('success', __('Group deleted successfully.'));
    }

    public function bulkEnrollCustomers(CustomerBulkGroupEnrollmentRequest $request)
    {
        $group = Group::query()->findOrFail($request->validated('group_id'));
        $result = $this->enrollCustomerIds($group, collect($request->validated('customer_ids')));

        return redirect()
            ->route('customers.index')
            ->with($result['added'] > 0 ? 'success' : 'error', $this->enrollmentMessage($result));
    }

    public function enrollCustomers(GroupCustomerStoreRequest $request, Group $group)
    {
        if (! in_array($group->status, [GroupStatus::Planned->value, GroupStatus::Active->value], true)) {
            return redirect()
                ->route('groups.show', $group)
                ->with('error', __('Customers can only be added to planned or active groups.'));
        }

        $result = $this->enrollCustomerIds($group, collect($request->validated('customer_ids')));

        return redirect()
            ->route('groups.show', $group)
            ->with($result['added'] > 0 ? 'success' : 'error', $this->enrollmentMessage($result));
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
            'levels' => Level::query()
                ->orderBy('name')
                ->get(),
            'packages' => Package::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
            'statuses' => GroupStatus::options(),
            'users' => User::query()
                ->orderBy('name')
                ->get(),
            'weekDays' => $this->weekDays(),
        ];
    }

    /**
     * @return Collection<int, Customer>
     */
    private function availableCustomers(Group $group): Collection
    {
        return Customer::query()
            ->where('status', 'active')
            ->whereDoesntHave('groupEnrollments', fn ($query) => $query->where('group_id', $group->id))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    /**
     * @param  Collection<int, mixed>  $customerIds
     * @return array{added: int, skipped: int}
     */
    private function enrollCustomerIds(Group $group, Collection $customerIds): array
    {
        return DB::transaction(function () use ($group, $customerIds) {
            $customerIds = $customerIds
                ->map(fn ($customerId) => (int) $customerId)
                ->unique()
                ->values();

            $existingCustomerIds = $group->groupEnrollments()
                ->whereIn('customer_id', $customerIds)
                ->pluck('customer_id');

            $newCustomerIds = $customerIds
                ->diff($existingCustomerIds)
                ->values();

            $capacitySkipped = 0;

            if ($group->capacity) {
                $activeCount = $group->groupEnrollments()
                    ->where('status', GroupEnrollmentStatus::Active->value)
                    ->count();
                $availableSlots = max($group->capacity - $activeCount, 0);

                if ($newCustomerIds->count() > $availableSlots) {
                    $capacitySkipped = $newCustomerIds->count() - $availableSlots;
                    $newCustomerIds = $newCustomerIds->take($availableSlots)->values();
                }
            }

            if ($newCustomerIds->isEmpty()) {
                return [
                    'added' => 0,
                    'skipped' => $existingCustomerIds->count() + $capacitySkipped,
                ];
            }

            $customerPackages = CustomerPackage::query()
                ->whereIn('customer_id', $newCustomerIds)
                ->where('status', 'active')
                ->when($group->package_id, fn ($query) => $query->where('package_id', $group->package_id))
                ->latest('created_at')
                ->get()
                ->unique('customer_id')
                ->keyBy('customer_id');

            foreach ($newCustomerIds as $customerId) {
                $group->groupEnrollments()->create([
                    'customer_id' => $customerId,
                    'customer_package_id' => $customerPackages->get($customerId)?->id,
                    'status' => GroupEnrollmentStatus::Active->value,
                    'joined_at' => now()->toDateString(),
                ]);
            }

            return [
                'added' => $newCustomerIds->count(),
                'skipped' => $existingCustomerIds->count() + $capacitySkipped,
            ];
        });
    }

    /**
     * @param  array{added: int, skipped: int}  $result
     */
    private function enrollmentMessage(array $result): string
    {
        if ($result['added'] > 0 && $result['skipped'] > 0) {
            return __(':added customers added to the group. :skipped were skipped.', $result);
        }

        if ($result['added'] > 0) {
            return trans_choice('{1} :count customer added to the group.|[2,*] :count customers added to the group.', $result['added'], [
                'count' => $result['added'],
            ]);
        }

        return __('No customers were added. They may already be in the group or the group is full.');
    }

    /**
     * @return array<string, string>
     */
    private function weekDays(): array
    {
        return collect(GroupStoreRequest::weekDayValues())
            ->mapWithKeys(fn (string $day) => [$day => __(Str::headline($day))])
            ->all();
    }
}
