<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerStoreRequest extends FormRequest
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
            'placement_month' => ['nullable', 'date'],
            'tester_id' => ['nullable', 'exists:users,id'],
            'old_instructor_id' => ['nullable', 'exists:users,id'],
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
            'level_id' => ['nullable', 'exists:levels,id'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->whereNotNull('parent_id')],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function customerData(): array
    {
        $validated = $this->validated();
        unset($validated['package_id'], $validated['package_assignments']);

        $validated['created_by'] = $this->user()?->id;
        $validated['wallet_balance'] = round((float) ($validated['wallet_balance'] ?? 0), 2);

        return $validated;
    }

    public function packageId(): ?int
    {
        $packageId = $this->validated('package_id');

        return $packageId ? (int) $packageId : null;
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
