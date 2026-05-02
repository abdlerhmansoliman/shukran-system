<?php

namespace App\Http\Requests;

use App\Enums\PackageStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackageStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', PackageStatus::Active->value),
        ]);
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
            'name' => ['required', 'string', 'max:255', Rule::unique('packages', 'name')],
            'levels_count' => ['required', 'integer', 'min:1', 'max:999'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'status' => ['required', Rule::in(PackageStatus::values())],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function packageData(): array
    {
        return $this->validated();
    }
}
