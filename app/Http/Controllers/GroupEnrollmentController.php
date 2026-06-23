<?php

namespace App\Http\Controllers;

use App\Enums\CustomerStatus;
use App\Enums\GroupEnrollmentStatus;
use App\Enums\GroupStatus;
use App\Http\Requests\CustomerBulkGroupEnrollmentRequest;
use App\Http\Requests\GroupCustomerStoreRequest;
use App\Models\Group;
use App\Models\GroupEnrollment;
use App\Services\GroupEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GroupEnrollmentController extends Controller
{
    public function __construct(
        private GroupEnrollmentService $groupEnrollmentService
    ) {}

    public function bulkStore(CustomerBulkGroupEnrollmentRequest $request)
    {
        $group = Group::query()->findOrFail($request->validated('group_id'));
        $result = $this->groupEnrollmentService->enrollCustomerIds($group, collect($request->validated('customer_ids')));

        return redirect()
            ->route('customers.index')
            ->with($result['added'] > 0 ? 'success' : 'error', $this->enrollmentMessage($result));
    }

    public function store(GroupCustomerStoreRequest $request, Group $group)
    {
        if (! in_array($group->status, [GroupStatus::Open->value, GroupStatus::Active->value], true)) {
            return redirect()
                ->route('groups.show', $group)
                ->with('error', __('Customers can only be added to planned or active groups.'));
        }
        
        $result = $this->groupEnrollmentService->enrollCustomerIds($group, collect($request->validated('customer_ids')));

        return redirect()
            ->route('groups.show', $group)
            ->with($result['added'] > 0 ? 'success' : 'error', $this->enrollmentMessage($result));
    }

    public function destroy(Group $group, GroupEnrollment $groupEnrollment)
    {
        abort_unless((int) $groupEnrollment->group_id === (int) $group->id, 404);

        $groupEnrollment->delete();

        return redirect()
            ->route('groups.show', $group)
            ->with('success', __('Customer removed from group successfully.'));
    }

    public function update(Request $request, Group $group, GroupEnrollment $groupEnrollment)
    {
        abort_unless((int) $groupEnrollment->group_id === (int) $group->id, 404);

        if ($group->status !== GroupStatus::Open->value) {
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
                    GroupEnrollmentStatus::Rejected->value,
                ]),
            ],
        ]);

        $groupEnrollment->update([
            'status' => $validated['status'],
            'left_at' => in_array($validated['status'], [
                GroupEnrollmentStatus::Cancelled->value,
                GroupEnrollmentStatus::Rejected->value,
            ], true) ? now()->toDateString() : null,
        ]);

        if ($validated['status'] === GroupEnrollmentStatus::Ready->value) {
            $groupEnrollment->customer()->update([
                'status' => CustomerStatus::Active->value,
            ]);
        }

        return redirect()
            ->route('groups.show', $group)
            ->with('success', __('Enrollment status updated successfully.'));
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
}
