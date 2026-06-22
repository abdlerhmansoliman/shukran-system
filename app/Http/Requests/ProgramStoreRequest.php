<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Program;

class ProgramStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', Rule::unique('programs', 'name')],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function programData(): array
    {
        return $this->validated();
    }
}
