@extends('layouts.app')

@section('content')
<div class="bg-slate-100/70 py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Attendance Management') }}</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ __('Monthly Summary') }}</h1>
                <p class="mt-2 text-sm text-slate-500">
                    {{ __('Overview of employee absences for :month/:year. Working days: :days', ['month' => $month, 'year' => $year, 'days' => $workingDays]) }}
                </p>
            </div>

            <a
                href="{{ route('attendance.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
            >
                {{ __('Daily View') }}
            </a>
        </div>

        {{-- Filters --}}
        <div class="mb-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="GET" action="{{ route('attendance.monthly') }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label for="summary_month" class="text-sm font-semibold text-slate-700">{{ __('Month') }}</label>
                    <select id="summary_month" name="month" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($month === $m)>{{ $m }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label for="summary_year" class="text-sm font-semibold text-slate-700">{{ __('Year') }}</label>
                    <input id="summary_year" name="year" type="number" min="2000" max="2100" value="{{ $year }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                </div>

                <div>
                    <label for="summary_department" class="text-sm font-semibold text-slate-700">{{ __('Department') }}</label>
                    <select id="summary_department" name="department_id" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        <option value="">{{ __('All Departments') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected($selectedDepartmentId == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-[0.6rem] text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    {{ __('Filter') }}
                </button>
            </form>
        </div>

        {{-- Summary Table --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">
                    {{ __('Employee Attendance Summary') }}
                </p>
            </div>

            @if($summaries->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-sm text-slate-500">{{ __('No active employees found.') }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="whitespace-nowrap px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Employee') }}</th>
                                <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Department') }}</th>
                                <th class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Working Days') }}</th>
                                <th class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-emerald-600">{{ __('Present Days') }}</th>
                                <th class="whitespace-nowrap px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-rose-600">{{ __('Absent Days') }}</th>
                                <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Absence Details') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($summaries as $summary)
                                @php
                                    $emp = $summary['employee'];
                                    $hasAbsences = $summary['total_absent_days'] > 0;
                                @endphp
                                <tr class="{{ $hasAbsences ? 'bg-rose-50/30' : '' }}">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-lg {{ $hasAbsences ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }} text-xs font-semibold">
                                                @php
                                                    $initials = \Illuminate\Support\Str::of($emp->display_name)
                                                        ->explode(' ')
                                                        ->filter()
                                                        ->take(2)
                                                        ->map(fn (string $part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                                                        ->implode('');
                                                @endphp
                                                {{ $initials ?: 'NA' }}
                                            </div>
                                            <a href="{{ route('employees.show', $emp) }}" class="font-semibold text-slate-900 hover:text-slate-600">
                                                {{ $emp->display_name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">
                                        {{ $emp->department?->name ?: __('N/A') }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-center text-sm font-semibold text-slate-700">
                                        {{ $summary['working_days'] }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-center">
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                            {{ $summary['present_days'] }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-center">
                                        <span class="inline-flex items-center rounded-full {{ $hasAbsences ? 'bg-rose-100 text-rose-700 ring-rose-600/20' : 'bg-slate-100 text-slate-500 ring-slate-300/20' }} px-3 py-1 text-xs font-semibold ring-1 ring-inset">
                                            {{ $summary['total_absent_days'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        @if($summary['absences']->isNotEmpty())
                                            <div class="flex flex-wrap gap-1.5">
                                                @foreach($summary['absences'] as $absence)
                                                    <span class="inline-flex items-center rounded-lg bg-rose-50 px-2 py-1 text-[11px] font-medium text-rose-600 ring-1 ring-inset ring-rose-200" title="{{ $absence->reason }}">
                                                        {{ $absence->date->format('d') }} — {{ $absence->type->label() }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400">{{ __('Perfect attendance') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
