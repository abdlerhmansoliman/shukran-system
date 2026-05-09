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
use App\Models\GroupEnrollment;
use App\Models\Level;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        $data = $request->groupData();

        if (($data['status'] ?? null) === GroupStatus::Active->value && $group->status !== GroupStatus::Active->value) {
            $startBlockerMessage = $this->groupStartBlockerMessage($group);

            if ($startBlockerMessage) {
                return back()
                    ->withInput()
                    ->withErrors(['status' => $startBlockerMessage])
                    ->with('error', $startBlockerMessage);
            }
        }

        DB::transaction(function () use ($data, $group) {
            $group->update($data);

            if ($group->status === GroupStatus::Active->value) {
                $this->activateReadyEnrollments($group);
            }
        });

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

    public function destroyCustomer(Group $group, GroupEnrollment $groupEnrollment)
    {
        abort_unless((int) $groupEnrollment->group_id === (int) $group->id, 404);

        $groupEnrollment->delete();

        return redirect()
            ->route('groups.show', $group)
            ->with('success', __('Customer removed from group successfully.'));
    }

    public function updateCustomerStatus(Request $request, Group $group, GroupEnrollment $groupEnrollment)
    {
        abort_unless((int) $groupEnrollment->group_id === (int) $group->id, 404);

        if ($group->status !== GroupStatus::Planned->value) {
            return redirect()
                ->route('groups.show', $group)
                ->with('error', __('Enrollment confirmation can only be changed while the group is planned.'));
        }

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    GroupEnrollmentStatus::Pending->value,
                    GroupEnrollmentStatus::Ready->value,
                    GroupEnrollmentStatus::Cancelled->value,
                ]),
            ],
        ]);

        $groupEnrollment->update([
            'status' => $validated['status'],
            'left_at' => $validated['status'] === GroupEnrollmentStatus::Cancelled->value ? now()->toDateString() : null,
        ]);

        return redirect()
            ->route('groups.show', $group)
            ->with('success', __('Enrollment status updated successfully.'));
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
            'levels' => Level::query()
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
            ->when($group->category_id, fn ($query) => $query->where('category_id', $group->category_id))
            ->whereDoesntHave('groupEnrollments', fn ($query) => $query->where('group_id', $group->id))
            ->whereHas('customerPackages', function ($builder) {
                $builder
                    ->where('status', 'active')
                    ->whereDoesntHave('groupEnrollments', fn ($query) => $query->whereIn('status', GroupEnrollmentStatus::reservedValues()));
            })
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
                    ->whereIn('status', GroupEnrollmentStatus::reservedValues())
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
                ->whereDoesntHave('groupEnrollments', fn ($query) => $query->whereIn('status', GroupEnrollmentStatus::reservedValues()))
                ->whereHas('customer', function ($query) use ($group) {
                    $query->when($group->category_id, fn ($builder) => $builder->where('category_id', $group->category_id));
                })
                ->latest('created_at')
                ->get()
                ->unique('customer_id')
                ->keyBy('customer_id');

            $missingSubscriptionCount = $newCustomerIds->diff($customerPackages->keys())->count();
            $newCustomerIds = $newCustomerIds
                ->filter(fn ($customerId) => $customerPackages->has($customerId))
                ->values();

            foreach ($newCustomerIds as $customerId) {
                $status = $this->newEnrollmentStatus($group);

                $group->groupEnrollments()->create([
                    'customer_id' => $customerId,
                    'customer_package_id' => $customerPackages->get($customerId)?->id,
                    'status' => $status,
                    'joined_at' => $status === GroupEnrollmentStatus::Active->value ? now()->toDateString() : null,
                ]);
            }

            return [
                'added' => $newCustomerIds->count(),
                'skipped' => $existingCustomerIds->count() + $capacitySkipped + $missingSubscriptionCount,
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

    private function groupStartBlockerMessage(Group $group): ?string
    {
        $nonReadyEnrollmentsCount = $group->groupEnrollments()
            ->whereNotIn('status', [
                GroupEnrollmentStatus::Ready->value,
                GroupEnrollmentStatus::Cancelled->value,
            ])
            ->count();

        if ($nonReadyEnrollmentsCount > 0) {
            return trans_choice(
                '{1} The group cannot start because 1 customer is not ready.|[2,*] The group cannot start because :count customers are not ready.',
                $nonReadyEnrollmentsCount,
                ['count' => $nonReadyEnrollmentsCount]
            );
        }

        return null;
    }

    private function activateReadyEnrollments(Group $group): void
    {
        $group->groupEnrollments()
            ->where('status', GroupEnrollmentStatus::Ready->value)
            ->update([
                'status' => GroupEnrollmentStatus::Active->value,
                'joined_at' => now()->toDateString(),
                'left_at' => null,
            ]);
    }

    private function newEnrollmentStatus(Group $group): string
    {
        return $group->status === GroupStatus::Active->value
            ? GroupEnrollmentStatus::Active->value
            : GroupEnrollmentStatus::Pending->value;
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
