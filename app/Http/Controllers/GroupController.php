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
use App\Models\Package;
use App\Models\User;
use App\Services\GroupEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    public function __construct(
        private GroupEnrollmentService $groupEnrollmentService
    ) {}

    public function index(GroupDataTable $datatable)
    {
        Gate::authorize('view groups');
        return $datatable->render('groups.index');
    }

    public function create()
    {
        Gate::authorize('create groups');
        return view('groups.create', $this->formData());
    }

    public function store(GroupStoreRequest $request)
    {
        Gate::authorize('create groups');
        $group = Group::query()->create($request->groupData());

        return redirect()
            ->route('groups.show', $group)
            ->with('success', __('Group created successfully.'));
    }

    public function availableInstructors(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $startTime = $request->query('start_time');
        $endTime = $request->query('end_time');
        $daysOfWeek = (array) $request->query('days_of_week', []);
        $groupId = $request->query('group_id');

        if (!$startDate || !$endDate || !$startTime || !$endTime) {
            return response()->json(User::query()->orderBy('name')->get(['id', 'name']));
        }

        $conflictingInstructorIds = Group::query()
            ->whereIn('status', [GroupStatus::Active->value, GroupStatus::Open->value])
            ->when($groupId, fn($q) => $q->where('id', '!=', $groupId))
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->get()
            ->filter(fn($g) => !empty(array_intersect($g->days_of_week ?? [], $daysOfWeek)))
            ->pluck('instructor_id')
            ->unique()
            ->filter();

        $availableInstructors = User::query()
            ->whereNotIn('id', $conflictingInstructorIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($availableInstructors);
    }

    public function show(Group $group)
    {
        Gate::authorize('view groups');
        $group->load([
            'level',
            'instructor',
            'groupEnrollments' => fn ($query) => $query->latest('joined_at')->latest(),
            'groupEnrollments.customer',
            'groupEnrollments.customerPackage.package',
        ]);

        return view('groups.show', [
            'group' => $group,
            'availableCustomers' => $this->groupEnrollmentService->availableCustomers($group),
            'canAddCustomers' => in_array($group->status, [GroupStatus::Open->value, GroupStatus::Active->value], true),
        ]);
    }

    public function edit(Group $group)
    {
        Gate::authorize('edit groups');
        return view('groups.edit', [
            'group' => $group,
            ...$this->formData(),
        ]);
    }

    public function update(GroupUpdateRequest $request, Group $group)
    {
        Gate::authorize('edit groups');
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
        Gate::authorize('delete groups');
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

    private function groupStartBlockerMessage(Group $group): ?string
    {
        $nonReadyEnrollmentsCount = $group->groupEnrollments()
            ->whereNotIn('status', [
                GroupEnrollmentStatus::Ready->value,
                GroupEnrollmentStatus::Cancelled->value,
                GroupEnrollmentStatus::Rejected->value,
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
