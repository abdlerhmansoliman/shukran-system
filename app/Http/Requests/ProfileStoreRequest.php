<?php

namespace App\Http\Requests;

use App\Enums\CustomerKeyword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'entry_level_id' => ['nullable', 'exists:levels,id'],
            'current_level_id' => ['nullable', 'exists:levels,id'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->whereNotNull('parent_id')],
            'tester_id' => ['nullable', 'exists:users,id'],
            'placement_month' => ['nullable', 'date'],
            'job' => ['nullable', 'string', 'max:255'],
            'college' => ['nullable', 'string', 'max:255'],
            'progress_report_link' => ['nullable', 'string', 'max:2000'],
            'test_date' => ['nullable', 'date'],
            'agreed_package_id' => ['nullable', Rule::exists('packages', 'id')->where('status', 'active')],
            'agreed_amount' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'keywords' => ['nullable', Rule::in(CustomerKeyword::values())],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function profileData(): array
    {
        $validated = $this->validated();

        if (filled($validated['entry_level_id'] ?? null) && blank($validated['current_level_id'] ?? null)) {
            $validated['current_level_id'] = $validated['entry_level_id'];
        }

        return $validated;
    }
}
