<?php

namespace App\Enums;

enum GroupStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Active = 'active';
    case Inactive = 'inactive';
    case Finished = 'finished';
    case Hold = 'hold';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Open => __('Open'),
            self::Active => __('Active'),
            self::Inactive => __('Inactive'),
            self::Finished => __('Finished'),
            self::Hold => __('Hold'),
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
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}
