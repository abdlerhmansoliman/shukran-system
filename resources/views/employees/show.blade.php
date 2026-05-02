@extends('layouts.app')

@section('content')
@php
    $name = $employee->user?->name ?: __('Unnamed employee');
    $initials = \Illuminate\Support\Str::of($name)
        ->explode(' ')
        ->filter()
        ->take(2)
        ->map(fn (string $part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('');
    $latestPayroll = $employee->payrolls->first();
    $latestReport = $employee->monthlyReports->first();
    $adjustments = $employee->adjustments->take(5);
@endphp

<div class="bg-slate-100/70 py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Employee Profile') }}</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ $name }}</h1>
                <p class="mt-2 text-sm text-slate-500">
                    {{ __('View account, employment, payroll, and recent work-report details for this employee.') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a
                    href="{{ route('employees.edit', $employee) }}"
                    class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                >
                    {{ __('Edit Employee') }}
                </a>
                <a
                    href="{{ route('employees.index') }}"
                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    {{ __('Back to Employees') }}
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="space-y-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-slate-900 text-2xl font-semibold text-white shadow-sm">
                                {{ $initials ?: 'NA' }}
                            </div>

                            <div>
                                <h2 class="text-2xl font-semibold text-slate-900">{{ $name }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ $employee->user?->email ?: __('No email provided') }}</p>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $employee->status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/20' }}">
                                        {{ __(\Illuminate\Support\Str::headline($employee->status)) }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 ring-1 ring-inset ring-sky-600/20">
                                        {{ $employee->department?->name ?: __('Not assigned') }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                        {{ __(\Illuminate\Support\Str::headline($employee->salary_type)) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                <p class="font-medium text-slate-700">{{ __('Employee ID') }}</p>
                                <p class="mt-1">#{{ $employee->id }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                <p class="font-medium text-slate-700">{{ __('Basic Salary') }}</p>
                                <p class="mt-1">{{ number_format((float) $employee->basic_salary, 2) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                <p class="font-medium text-slate-700">{{ __('Hire Date') }}</p>
                                <p class="mt-1">{{ $employee->hire_date?->format('M d, Y') ?: __('Not specified') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Contact Information') }}</p>
                        <div class="mt-5 space-y-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Phone') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $employee->phone ?: __('Not specified') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Email') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $employee->user?->email ?: __('No email provided') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Age') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $employee->age ?: __('Not specified') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Employment Details') }}</p>
                        <div class="mt-5 space-y-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Department') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $employee->department?->name ?: __('Not assigned') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Job Title') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $employee->job_title ?: __('Not specified') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Status') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ __(\Illuminate\Support\Str::headline($employee->status)) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Payroll Snapshot') }}</p>

                    @if($latestPayroll)
                        <div class="mt-5 grid gap-4 md:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Period') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $latestPayroll->month }}/{{ $latestPayroll->year }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Net Salary') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ number_format((float) $latestPayroll->net_salary, 2) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Payment Status') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ __(\Illuminate\Support\Str::headline($latestPayroll->status)) }}</p>
                            </div>
                        </div>
                    @else
                        <div class="mt-5 rounded-3xl bg-slate-50 p-5">
                            <p class="text-sm leading-7 text-slate-600">{{ __('No payroll records are available for this employee yet.') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Latest Work Report') }}</p>

                    @if($latestReport)
                        <div class="mt-5 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Period') }}</p>
                                    <p class="mt-2 text-base font-semibold text-slate-900">{{ $latestReport->month }}/{{ $latestReport->year }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Worked Hours') }}</p>
                                    <p class="mt-2 text-base font-semibold text-slate-900">{{ number_format((float) $latestReport->actual_worked_hours, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Required Hours') }}</p>
                                    <p class="mt-2 text-base font-semibold text-slate-900">{{ number_format((float) $latestReport->required_working_hours, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Overtime Hours') }}</p>
                                    <p class="mt-2 text-base font-semibold text-slate-900">{{ number_format((float) $latestReport->overtime_hours, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mt-5 rounded-3xl bg-slate-50 p-5">
                            <p class="text-sm leading-7 text-slate-600">{{ __('No work reports are available for this employee yet.') }}</p>
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Recent Adjustments') }}</p>
                        <span class="text-sm font-medium text-slate-500">{{ $employee->adjustments->count() }}</span>
                    </div>

                    @if($adjustments->isEmpty())
                        <div class="mt-5 rounded-3xl bg-slate-50 p-5">
                            <p class="text-sm leading-7 text-slate-600">{{ __('No adjustments are available for this employee yet.') }}</p>
                        </div>
                    @else
                        <div class="mt-5 space-y-3">
                            @foreach($adjustments as $adjustment)
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $adjustment->reason ?: __(\Illuminate\Support\Str::headline($adjustment->type)) }}</p>
                                            <p class="mt-1 text-sm text-slate-500">{{ $adjustment->month }}/{{ $adjustment->year }}</p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $adjustment->type === 'bonus' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-rose-50 text-rose-700 ring-rose-600/20' }}">
                                            {{ number_format((float) $adjustment->amount, 2) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Record Timeline') }}</p>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Created At') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $employee->created_at?->format('M d, Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Last Updated') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $employee->updated_at?->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
