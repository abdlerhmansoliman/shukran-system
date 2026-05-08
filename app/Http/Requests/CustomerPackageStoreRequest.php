<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerPackageStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('quantity')) {
            $this->merge([
                'quantity' => 1,
            ]);
        }
    }

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
            'package_id' => ['required', Rule::exists('packages', 'id')->where('status', 'active')],
            'quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function packageId(): int
    {
        return (int) $this->validated('package_id');
    }

    public function quantity(): int
    {
        return (int) $this->validated('quantity');
    }
}
