<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Program;

class ProgramUpdateRequest extends FormRequest
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
        $program = $this->route('program');
        $programId = $program instanceof Program ? $program->id : null;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('programs', 'name')->ignore($programId)],
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
