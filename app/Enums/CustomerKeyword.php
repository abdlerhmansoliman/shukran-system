<?php

namespace App\Enums;

enum CustomerKeyword: string
{
    case Analytical = 'analytical';
    case Driver = 'driver';
    case Amiable = 'amiable';
    case Expressive = 'expressive';

    public function label(): string
    {
        return match ($this) {
            self::Analytical => __('Analytical'),
            self::Driver => __('Driver'),
            self::Amiable => __('Amiable'),
            self::Expressive => __('Expressive'),
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
            ->mapWithKeys(fn (self $keyword) => [$keyword->value => $keyword->label()])
            ->all();
    }
}
