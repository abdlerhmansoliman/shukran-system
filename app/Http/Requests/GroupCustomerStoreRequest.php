<?php

namespace App\Http\Requests;

use App\Enums\GroupEnrollmentStatus;
use App\Models\CustomerPackage;
use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GroupCustomerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'customer_ids' => ['required', 'array', 'min:1'],
            'customer_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('customers', 'id'),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $group = $this->route('group');

            if (! $group instanceof Group) {
                return;
            }

            $this->validateCustomersMatchCategoryAndHaveSubscription($validator, $group);
        });
    }

    private function validateCustomersMatchCategoryAndHaveSubscription($validator, Group $group): void
    {
        $customerIds = collect($this->input('customer_ids', []))
            ->map(fn ($customerId) => (int) $customerId)
            ->filter()
            ->unique()
            ->values();

        if ($customerIds->isEmpty()) {
            return;
        }

        $eligibleCustomerIds = CustomerPackage::query()
            ->whereIn('customer_id', $customerIds)
            ->where('status', 'active')
            ->whereDoesntHave('groupEnrollments', fn ($query) => $query->where('status', GroupEnrollmentStatus::Active->value))
            ->whereHas('customer', function ($query) use ($group) {
                $query->when($group->category_id, fn ($builder) => $builder->where('category_id', $group->category_id));
            })
            ->pluck('customer_id')
            ->map(fn ($customerId) => (int) $customerId);

        if ($customerIds->diff($eligibleCustomerIds)->isNotEmpty()) {
            $validator->errors()->add('customer_ids', __('Selected customers must match the group category and have an available active subscription.'));
        }
    }
}
