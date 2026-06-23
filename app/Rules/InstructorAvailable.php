<?php

namespace App\Rules;

use App\Enums\GroupStatus;
use App\Models\Group;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class InstructorAvailable implements ValidationRule
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private array $data,
        private ?int $ignoreGroupId = null
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $instructorId = $value;

        if (!$instructorId || 
            empty($this->data['start_date']) || 
            empty($this->data['end_date']) || 
            empty($this->data['start_time']) || 
            empty($this->data['end_time'])
        ) {
            return;
        }

        $inputDays = (array) ($this->data['days_of_week'] ?? []);

        $conflict = Group::query()
            ->where('instructor_id', $instructorId)
            ->whereIn('status', [GroupStatus::Active->value, GroupStatus::Draft->value])
            ->when($this->ignoreGroupId, fn($q) => $q->where('id', '!=', $this->ignoreGroupId))
            ->where('start_date', '<=', $this->data['end_date'])
            ->where('end_date', '>=', $this->data['start_date'])
            ->where('start_time', '<', $this->data['end_time'])
            ->where('end_time', '>', $this->data['start_time'])
            ->get()
            ->filter(function ($group) use ($inputDays) {
                return !empty(array_intersect($group->days_of_week ?? [], $inputDays));
            })
            ->first();

        if ($conflict) {
            $fail(__('The instructor is already busy with group ":name" during the selected time.', [
                'name' => $conflict->name
            ]));
        }
    }
}
