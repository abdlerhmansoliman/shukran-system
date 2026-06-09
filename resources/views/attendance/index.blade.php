@extends('layouts.app')

@section('content')
<div class="bg-slate-100/70 py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Attendance Management') }}</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ __('Daily Attendance') }}</h1>
                <p class="mt-2 text-sm text-slate-500">
                    {{ __('Employees are present by default. Only mark those who are absent.') }}
                </p>
            </div>

            <a
                href="{{ route('attendance.monthly', ['month' => \Carbon\Carbon::parse($date)->month, 'year' => \Carbon\Carbon::parse($date)->year]) }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
            >
                {{ __('Monthly Summary') }}
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-700">
                {{ __('Please review the highlighted fields and try again.') }}
                <ul class="mt-2 list-inside list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filters --}}
        <div class="mb-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="GET" action="{{ route('attendance.index') }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label for="attendance_date" class="text-sm font-semibold text-slate-700">{{ __('Date') }}</label>
                    <input
                        id="attendance_date"
                        name="date"
                        type="date"
                        value="{{ $date }}"
                        max="{{ now()->toDateString() }}"
                        class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10"
                    >
                </div>

                <div>
                    <label for="attendance_department" class="text-sm font-semibold text-slate-700">{{ __('Department') }}</label>
                    <select
                        id="attendance_department"
                        name="department_id"
                        class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10"
                    >
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

        {{-- Stats Bar --}}
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Total Employees') }}</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $totalEmployees }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-[0.18em] text-emerald-600">{{ __('Present') }}</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $presentCount }}</p>
            </div>
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-[0.18em] text-rose-600">{{ __('Absent') }}</p>
                <p class="mt-2 text-2xl font-bold text-rose-700">{{ $absentCount }}</p>
            </div>
        </div>

        {{-- Employee Attendance List --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">
                    {{ __('Attendance for :date', ['date' => \Carbon\Carbon::parse($date)->format('l, M d, Y')]) }}
                </p>
            </div>

            @if($employees->isEmpty())
                <div class="p-8 text-center">
                    <p class="text-sm text-slate-500">{{ __('No active employees found.') }}</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($employees as $employee)
                        @php
                            $absence = $employee->absences->first();
                            $isAbsent = $absence !== null;
                        @endphp
                        <div class="flex flex-col gap-4 px-6 py-4 sm:flex-row sm:items-center sm:justify-between {{ $isAbsent ? 'bg-rose-50/50' : '' }}">
                            {{-- Employee Info --}}
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $isAbsent ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }} text-sm font-semibold">
                                    @php
                                        $initials = \Illuminate\Support\Str::of($employee->display_name)
                                            ->explode(' ')
                                            ->filter()
                                            ->take(2)
                                            ->map(fn (string $part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
                                            ->implode('');
                                    @endphp
                                    {{ $initials ?: 'NA' }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $employee->display_name }}</p>
                                    <p class="text-sm text-slate-500">{{ $employee->department?->name ?: __('No department') }}</p>
                                </div>
                            </div>

                            {{-- Status & Actions --}}
                            <div class="flex items-center gap-3">
                                @if($isAbsent)
                                    {{-- Show absence type badge --}}
                                    <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-600/20">
                                        {{ $absence->type->label() }}
                                        @if($absence->reason)
                                            <span class="ml-1 text-rose-500">— {{ $absence->reason }}</span>
                                        @endif
                                    </span>

                                    {{-- Remove absence (mark present) --}}
                                    <form method="POST" action="{{ route('attendance.destroy', $absence) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex items-center rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                            title="{{ __('Mark Present') }}"
                                        >
                                            <svg class="mr-1 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            {{ __('Mark Present') }}
                                        </button>
                                    </form>
                                @else
                                    {{-- Present badge --}}
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                        <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        {{ __('Present') }}
                                    </span>

                                    {{-- Mark Absent form (inline) --}}
                                    <div x-data="{ open: false }" class="relative">
                                        <button
                                            @click="open = !open"
                                            type="button"
                                            class="inline-flex items-center rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100"
                                        >
                                            <svg class="mr-1 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            {{ __('Mark Absent') }}
                                        </button>

                                        {{-- Dropdown form --}}
                                        <div
                                            x-show="open"
                                            @click.away="open = false"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute right-0 z-20 mt-2 w-72 rounded-2xl border border-slate-200 bg-white p-4 shadow-lg"
                                            x-cloak
                                        >
                                            <form method="POST" action="{{ route('attendance.store') }}">
                                                @csrf
                                                <input type="hidden" name="date" value="{{ $date }}">
                                                <input type="hidden" name="absences[0][employee_id]" value="{{ $employee->id }}">

                                                <div class="space-y-3">
                                                    <div>
                                                        <label class="text-xs font-semibold text-slate-600">{{ __('Absence Type') }}</label>
                                                        <select
                                                            name="absences[0][type]"
                                                            required
                                                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10"
                                                        >
                                                            @foreach($absenceTypes as $value => $label)
                                                                <option value="{{ $value }}">{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div>
                                                        <label class="text-xs font-semibold text-slate-600">{{ __('Reason') }} <span class="text-slate-400">({{ __('optional') }})</span></label>
                                                        <input
                                                            type="text"
                                                            name="absences[0][reason]"
                                                            maxlength="255"
                                                            class="mt-1 block w-full rounded-lg border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10"
                                                            placeholder="{{ __('e.g. Sick leave, Personal') }}"
                                                        >
                                                    </div>

                                                    <button
                                                        type="submit"
                                                        class="inline-flex w-full items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700"
                                                    >
                                                        {{ __('Confirm Absence') }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
