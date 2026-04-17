<?php

namespace App\Http\Requests;

use App\Enums\CustomerStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('status')) {
            $this->merge([
                'status' => CustomerStatus::Inactive->value,
            ]);
        }
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
            'status' => ['required', Rule::in(CustomerStatus::values())],
            'source' => ['nullable', 'string', 'max:255'],
            'customer_type' => ['required', Rule::in(['new', 'old'])],
            'placement_month' => ['nullable', 'date'],
            'tester_id' => ['nullable', 'exists:users,id'],
            'old_instructor_id' => ['nullable', 'exists:users,id'],
            'package_id' => ['nullable', 'exists:packages,id'],
            'age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'address' => ['nullable', 'string', 'max:255'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'level_id' => ['nullable', 'exists:levels,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function customerData(): array
    {
        $validated = $this->validated();
        unset($validated['package_id']);

        $validated['created_by'] = $this->user()?->id;

        return $validated;
    }
}
