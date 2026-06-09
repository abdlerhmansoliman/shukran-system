<?php

namespace App\Enums;

enum AbsenceType: string
{
    case FullDay = 'full_day';
    case HalfDay = 'half_day';
    case Late = 'late';

    public function label(): string
    {
        return match ($this) {
            self::FullDay => __('Full Day'),
            self::HalfDay => __('Half Day'),
            self::Late => __('Late'),
        };
    }

    /**
     * How much of a working day this absence costs.
     */
    public function deductionWeight(): float
    {
        return match ($this) {
            self::FullDay => 1.0,
            self::HalfDay => 0.5,
            self::Late => 0.25,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->all();
    }
}
