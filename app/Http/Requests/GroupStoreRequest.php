<?php

namespace App\Http\Requests;

use App\Enums\GroupStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Rules\InstructorAvailable;

class GroupStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', GroupStatus::Planned->value),
            'days_of_week' => collect($this->input('days_of_week', []))
                ->filter()
                ->values()
                ->all(),
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
            'name' => ['required', 'string', 'max:255'],
            'level_id' => ['nullable', 'exists:levels,id'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->whereNotNull('parent_id')],
            'instructor_id' => [
                'nullable', 
                'exists:users,id',
                new InstructorAvailable($this->all())
            ],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'days_of_week' => ['nullable', 'array'],
            'days_of_week.*' => ['string', Rule::in(self::weekDayValues())],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'status' => ['required', Rule::in(GroupStatus::values())],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function groupData(): array
    {
        $validated = $this->validated();
        $validated['days_of_week'] = $validated['days_of_week'] ?? null;

        return $validated;
    }

    /**
     * @return array<int, string>
     */
    public static function weekDayValues(): array
    {
        return ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    }
}
