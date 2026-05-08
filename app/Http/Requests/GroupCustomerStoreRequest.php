<?php

namespace App\Http\Requests;

use App\Enums\CustomerStatus;
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
                Rule::exists('customers', 'id')->where('status', CustomerStatus::Active->value),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $group = $this->route('group');

            if (! $group instanceof Group || ! $group->package_id) {
                return;
            }

            $this->validateCustomersHaveGroupPackage($validator, $group);
        });
    }

    private function validateCustomersHaveGroupPackage($validator, Group $group): void
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
            ->where('package_id', $group->package_id)
            ->where('status', 'active')
            ->pluck('customer_id')
            ->map(fn ($customerId) => (int) $customerId);

        if ($customerIds->diff($eligibleCustomerIds)->isNotEmpty()) {
            $validator->errors()->add('customer_ids', __('Selected customers must have the group package active before enrollment.'));
        }
    }
}
