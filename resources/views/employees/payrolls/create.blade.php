@extends('layouts.app')

@section('content')
@php
    $name = $employee->display_name;
    $month = (int) $month;
    $year = (int) $year;
    
    // Use the specific records for this month/year if they exist
    $report = $currentReport;
    $payroll = $currentPayroll;

    $requiredHours = old('required_working_hours', $report?->required_working_hours ?? 0);
    $actualHours = old('actual_worked_hours', $report?->actual_worked_hours ?? 0);
    $overtimeHours = old('overtime_hours', $report?->overtime_hours ?? 0);
    
    $hourSalary = $employee->salary_type === 'hourly'
        ? (float) $employee->basic_salary
        : ((float) $requiredHours > 0 ? round((float) $employee->basic_salary / (float) $requiredHours, 2) : 0);
    
    // Only pre-fill overtime amount from the specific payroll if it exists, otherwise calculate from hours
    $defaultOvertimeAmount = $payroll ? (float) $payroll->overtime_amount : round((float) $overtimeHours * (float) $hourSalary, 2);

    // Attendance-based defaults
    $attendanceWorkingDays = $attendanceSummary['working_days'] ?? 0;
    $attendancePresentDays = $attendanceSummary['present_days'] ?? 0;
    $attendanceAbsentDays = $attendanceSummary['total_absent_days'] ?? 0;
    $attendanceAbsences = $attendanceSummary['absences'] ?? collect();
    $defaultAbsenceDeduction = $absenceDeduction ?? 0;
@endphp

<div class="bg-slate-100/70 py-10">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Employee Payroll') }}</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ __('Generate Payroll') }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ __('Create or update a monthly payroll record for :name.', ['name' => $name]) }}</p>
            </div>

            <a
                href="{{ route('employees.show', $employee) }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
            >
                {{ __('Back to Employee') }}
            </a>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-700">
                {{ __('Please review the highlighted fields and try again.') }}
            </div>
        @endif

        <form method="POST" action="{{ route('employees.payrolls.store', $employee) }}" class="space-y-6">
            @csrf

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Employee') }}</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $name }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Salary Type') }}</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ __(\Illuminate\Support\Str::headline($employee->salary_type)) }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Basic Salary') }}</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $employee->basic_salary, 2) }}</p>
                    </div>
                </div>
            </div>

            {{-- Attendance Summary Card --}}
            <div class="rounded-3xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-amber-600">{{ __('Attendance Summary') }}</p>
                <p class="mt-1 text-xs text-amber-500">{{ __('Auto-calculated from recorded absences for this period.') }}</p>

                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl bg-white/70 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Working Days') }}</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ $attendanceWorkingDays }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/70 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-emerald-600">{{ __('Present Days') }}</p>
                        <p class="mt-2 text-sm font-bold text-emerald-700">{{ $attendancePresentDays }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/70 px-4 py-3">
                        <p class="text-xs font-medium uppercase tracking-[0.18em] text-rose-600">{{ __('Absent Days') }}</p>
                        <p class="mt-2 text-sm font-bold text-rose-700">{{ $attendanceAbsentDays }}</p>
                    </div>
                </div>

                @if($attendanceAbsences->isNotEmpty())
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @foreach($attendanceAbsences as $absence)
                            <span class="inline-flex items-center rounded-lg bg-white/70 px-2 py-1 text-[11px] font-medium text-amber-700 ring-1 ring-inset ring-amber-200" title="{{ $absence->reason }}">
                                {{ $absence->date->format('M d') }} — {{ $absence->type->label() }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Payroll Period') }}</p>

                <div class="mt-6 grid gap-5 sm:grid-cols-3">
                    <div>
                        <label for="payroll_month" class="text-sm font-semibold text-slate-700">{{ __('Month') }}</label>
                        <select id="payroll_month" name="month" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10" onchange="this.form.method='GET'; this.form.action='{{ route('employees.payrolls.create', $employee) }}'; this.form.submit();">
                            @for($number = 1; $number <= 12; $number++)
                                <option value="{{ $number }}" @selected($month === $number)>{{ $number }}</option>
                            @endfor
                        </select>
                        @error('month')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="payroll_year" class="text-sm font-semibold text-slate-700">{{ __('Year') }}</label>
                        <input id="payroll_year" name="year" type="number" min="2000" max="2100" value="{{ $year }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10" onchange="this.form.method='GET'; this.form.action='{{ route('employees.payrolls.create', $employee) }}'; this.form.submit();">
                        @error('year')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="payroll_status" class="text-sm font-semibold text-slate-700">{{ __('Status') }}</label>
                        <select id="payroll_status" name="status" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                            @foreach(['draft' => __('Draft'), 'paid' => __('Paid')] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $payroll?->status ?? 'draft') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Work Report') }}</p>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="required_working_days" class="text-sm font-semibold text-slate-700">{{ __('Required Working Days') }}</label>
                        <input id="required_working_days" name="required_working_days" type="number" min="0" step="0.01" value="{{ old('required_working_days', $attendanceWorkingDays) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        @error('required_working_days')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="required_working_hours" class="text-sm font-semibold text-slate-700">{{ __('Required Working Hours') }}</label>
                        <input id="required_working_hours" name="required_working_hours" type="number" min="0" step="0.01" value="{{ $requiredHours }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        @error('required_working_hours')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="actual_worked_days" class="text-sm font-semibold text-slate-700">{{ __('Actual Worked Days') }}</label>
                        <input id="actual_worked_days" name="actual_worked_days" type="number" min="0" step="0.01" value="{{ old('actual_worked_days', $attendancePresentDays) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        @error('actual_worked_days')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="actual_worked_hours" class="text-sm font-semibold text-slate-700">{{ __('Actual Worked Hours') }}</label>
                        <input id="actual_worked_hours" name="actual_worked_hours" type="number" min="0" step="0.01" value="{{ $actualHours }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        @error('actual_worked_hours')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="overtime_hours" class="text-sm font-semibold text-slate-700">{{ __('Overtime Hours') }}</label>
                        <input id="overtime_hours" name="overtime_hours" type="number" min="0" step="0.01" value="{{ $overtimeHours }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        @error('overtime_hours')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="overtime_amount" class="text-sm font-semibold text-slate-700">{{ __('Overtime Amount') }}</label>
                        <input id="overtime_amount" name="overtime_amount" type="number" min="0" step="0.01" value="{{ old('overtime_amount', $defaultOvertimeAmount) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        @error('overtime_amount')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Adjustments') }}</p>

                <div class="mt-6 grid gap-5 sm:grid-cols-3">
                    <div>
                        <label for="absence_deduction" class="text-sm font-semibold text-slate-700">{{ __('Absence Deduction') }}</label>
                        <input id="absence_deduction" name="absence_deduction" type="number" min="0" step="0.01" value="{{ old('absence_deduction', $defaultAbsenceDeduction) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        @error('absence_deduction')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="total_bonus" class="text-sm font-semibold text-slate-700">{{ __('Bonus') }}</label>
                        <input id="total_bonus" name="total_bonus" type="number" min="0" step="0.01" value="{{ old('total_bonus', $payroll?->total_bonus ?? 0) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        @error('total_bonus')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="total_deductions" class="text-sm font-semibold text-slate-700">{{ __('Deduction') }}</label>
                        <input id="total_deductions" name="total_deductions" type="number" min="0" step="0.01" value="{{ old('total_deductions', $payroll?->total_deductions ?? 0) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        @error('total_deductions')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Notes') }}</p>
                <textarea id="payroll_notes" name="notes" rows="3" class="mt-6 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">{{ old('notes', $report?->notes) }}</textarea>
                @error('notes')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col-reverse gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-end">
                <a href="{{ route('employees.show', $employee) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    {{ __('Generate Payroll') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
