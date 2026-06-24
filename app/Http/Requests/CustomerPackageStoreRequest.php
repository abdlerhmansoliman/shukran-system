<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerPackageStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('levels_count')) {
            $this->merge([
                'levels_count' => 1,
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
            'discount_id' => ['nullable', Rule::exists('discounts', 'id')->where('status', 'active')],
            'levels_count' => ['required', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function packageId(): int
    {
        return (int) $this->validated('package_id');
    }

    public function levelsCount(): int
    {
        return (int) $this->validated('levels_count');
    }

    public function discountId(): ?int
    {
        $discountId = $this->validated('discount_id');

        return $discountId ? (int) $discountId : null;
    }
}
