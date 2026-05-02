<?php

namespace App\Http\Requests;

use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackageUpdateRequest extends FormRequest
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
        $package = $this->route('package');
        $packageId = $package instanceof Package ? $package->id : null;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('packages', 'name')->ignore($packageId)],
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
