<?php

namespace App\Enums;

enum CustomerStatus: string
{
    case New = 'new';
    case Active = 'active';
    case Inactive = 'inactive';
    case Waiting = 'waiting';
    case WaitingForAppointment = 'waiting_for_appointment';
    case Finished = 'finished';
    case Paused = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::New => __('New'),
            self::Active => __('Active'),
            self::Inactive => __('Inactive'),
            self::Waiting => __('Waiting'),
            self::WaitingForAppointment => __('Waiting For Appointment'),
            self::Finished => __('Finished'),
            self::Paused => __('On Hold'),
        };
    }

    /**
     * Badge color classes for UI rendering.
     */
    public function color(): string
    {
        return match ($this) {
            self::New => 'bg-sky-50 text-sky-700 ring-sky-600/20',
            self::Active => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            self::Inactive => 'bg-slate-100 text-slate-600 ring-slate-500/20',
            self::Waiting => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            self::WaitingForAppointment => 'bg-violet-50 text-violet-700 ring-violet-600/20',
            self::Finished => 'bg-rose-50 text-rose-700 ring-rose-600/20',
            self::Paused => 'bg-orange-50 text-orange-700 ring-orange-600/20',
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
