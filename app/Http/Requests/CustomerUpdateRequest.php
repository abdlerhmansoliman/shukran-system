<?php

namespace App\Http\Requests;

use App\Enums\CustomerKeyword;
use App\Enums\CustomerStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $legacyPackageIds = collect($this->input('package_ids', []))->filter()->values();

        $assignments = collect($this->input('package_assignments', []))
            ->filter(fn ($assignment) => is_array($assignment) && filled($assignment['package_id'] ?? null))
            ->map(fn ($assignment) => [
                'package_id' => (int) $assignment['package_id'],
                'discount_id' => filled($assignment['discount_id'] ?? null) ? (int) $assignment['discount_id'] : null,
                'levels_count' => filled($assignment['levels_count'] ?? null) ? (int) $assignment['levels_count'] : 1,
            ])
            ->values()
            ->all();

        if ($assignments === [] && $legacyPackageIds->isNotEmpty()) {
            $assignments = $legacyPackageIds
                ->map(fn ($packageId) => [
                    'package_id' => (int) $packageId,
                    'discount_id' => null,
                    'levels_count' => 1,
                ])
                ->all();
        }

        if ($assignments === [] && $this->filled('package_id')) {
            $assignments[] = [
                'package_id' => (int) $this->input('package_id'),
                'discount_id' => null,
                'levels_count' => 1,
            ];
        }

        $this->merge([
            'package_assignments' => $assignments,
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'second_phone_number' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'customer_type' => ['required', Rule::in(['new', 'old'])],
            'status' => ['required', Rule::in(CustomerStatus::values())],
            'placement_month' => ['nullable', 'date'],
            'tester_id' => ['nullable', 'exists:users,id'],
            'package_id' => ['nullable', Rule::exists('packages', 'id')->where('status', 'active')],
            'package_assignments' => ['nullable', 'array'],
            'package_assignments.*.package_id' => ['required', Rule::exists('packages', 'id')->where('status', 'active')],
            'package_assignments.*.discount_id' => ['nullable', Rule::exists('discounts', 'id')->where('status', 'active')],
            'package_assignments.*.levels_count' => ['required', 'integer', 'min:1', 'max:50'],
            'age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'wallet_balance' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'address' => ['nullable', 'string', 'max:255'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'entry_level_id' => ['nullable', 'exists:levels,id'],
            'current_level_id' => ['nullable', 'exists:levels,id'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->whereNotNull('parent_id')],
            'job' => ['nullable', 'string', 'max:255'],
            'college' => ['nullable', 'string', 'max:255'],
            'progress_report_link' => ['nullable', 'string', 'max:2000'],
            'test_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'agreed_package_id' => ['nullable', Rule::exists('packages', 'id')->where('status', 'active')],
            'agreed_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'keywords' => ['nullable', Rule::in(CustomerKeyword::values())],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function customerData(): array
    {
        $validated = $this->validated();

        $customerKeys = [
            'first_name',
            'last_name',
            'email',
            'phone',
            'second_phone_number',
            'source',
            'notes',
            'address',
            'country_id',
            'wallet_balance',
            'customer_type',
            'status',
        ];

        $data = collect($validated)->only($customerKeys)->toArray();
        $data['wallet_balance'] = round((float) ($data['wallet_balance'] ?? 0), 2);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function profileData(): array
    {
        $validated = $this->validated();

        $profileKeys = [
            'first_name',
            'last_name',
            'age',
            'gender',
            'entry_level_id',
            'current_level_id',
            'category_id',
            'tester_id',
            'placement_month',
            'job',
            'college',
            'progress_report_link',
            'test_date',
            'agreed_package_id',
            'agreed_amount',
            'keywords',
            'notes',
        ];

        $data = collect($validated)->only($profileKeys)->toArray();

        if (filled($data['entry_level_id'] ?? null) && blank($data['current_level_id'] ?? null)) {
            $data['current_level_id'] = $data['entry_level_id'];
        }

        return $data;
    }

    /**
     * @return array<int, array{package_id: int, levels_count: int}>
     */
    public function packageAssignments(): array
    {
        return collect($this->validated('package_assignments', []))
            ->map(fn (array $assignment) => [
                'package_id' => (int) $assignment['package_id'],
                'discount_id' => filled($assignment['discount_id'] ?? null) ? (int) $assignment['discount_id'] : null,
                'levels_count' => (int) $assignment['levels_count'],
            ])
            ->all();
    }
}
