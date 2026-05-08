<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerBulkGroupEnrollmentRequest extends FormRequest
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
            'group_id' => [
                'required',
                Rule::exists('groups', 'id')->where(fn ($query) => $query->whereIn('status', ['planned', 'active'])),
            ],
            'customer_ids' => ['required', 'array', 'min:1'],
            'customer_ids.*' => ['integer', 'distinct', 'exists:customers,id'],
        ];
    }
}
