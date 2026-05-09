<?php

namespace App\Enums;

enum GroupEnrollmentStatus: string
{
    case Pending = 'pending';
    case Ready = 'ready';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Dropped = 'dropped';
    case Transferred = 'transferred';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Ready => __('Ready'),
            self::Active => __('Active'),
            self::Completed => __('Completed'),
            self::Cancelled => __('Cancelled'),
            self::Dropped => __('Dropped'),
            self::Transferred => __('Transferred'),
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
     * These statuses reserve the subscription for a current group flow.
     *
     * @return array<int, string>
     */
    public static function reservedValues(): array
    {
        return [
            self::Pending->value,
            self::Ready->value,
            self::Active->value,
        ];
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
