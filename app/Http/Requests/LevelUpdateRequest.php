<?php

namespace App\Http\Requests;

use App\Models\Level;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LevelUpdateRequest extends FormRequest
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
        $level = $this->route('level');
        $levelId = $level instanceof Level ? $level->id : null;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('levels', 'name')->ignore($levelId)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function levelData(): array
    {
        return $this->validated();
    }
}
