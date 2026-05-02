<?php

namespace App\Enums;

enum EmployeeSalaryType: string
{
    case Monthly = 'monthly';
    case Hourly = 'hourly';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => __('Monthly'),
            self::Hourly => __('Hourly'),
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
