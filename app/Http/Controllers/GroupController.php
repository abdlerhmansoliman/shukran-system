<?php

namespace App\Http\Controllers;

use App\DataTables\GroupDataTable;
use App\Enums\GroupEnrollmentStatus;
use App\Enums\GroupStatus;
use App\Http\Requests\GroupStoreRequest;
use App\Http\Requests\GroupUpdateRequest;
use App\Models\Category;
use App\Models\Group;
use App\Models\Level;
use App\Models\User;
use App\Services\GroupEnrollmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    public function __construct(
        private GroupEnrollmentService $groupEnrollmentService
    ) {}

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
            'availableCustomers' => $this->groupEnrollmentService->availableCustomers($group),
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
                $this->groupEnrollmentService->activateReadyEnrollments($group);
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
