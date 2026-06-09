<?php

namespace App\Http\Requests;

use App\Enums\AbsenceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceStoreRequest extends FormRequest
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
            'date' => ['required', 'date', 'before_or_equal:today'],
            'absences' => ['required', 'array', 'min:1'],
            'absences.*.employee_id' => ['required', 'integer', Rule::exists('employees', 'id')],
            'absences.*.type' => ['required', Rule::in(AbsenceType::values())],
            'absences.*.reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<int, array{employee_id: int, type: string, reason: ?string}>
     */
    public function absenceData(): array
    {
        return $this->validated('absences');
    }

    public function absenceDate(): string
    {
        return $this->validated('date');
    }
}
