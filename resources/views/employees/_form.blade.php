@php
    $statusValue = old('status', $employee?->status ?? \App\Enums\EmployeeStatus::Active->value);
    $salaryTypeValue = old('salary_type', $employee?->salary_type ?? \App\Enums\EmployeeSalaryType::Monthly->value);
    $hireDate = old('hire_date', $employee?->hire_date?->format('Y-m-d'));
@endphp

<div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Account Information') }}</p>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="name" class="text-sm font-semibold text-slate-700">{{ __('Name') }}</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $employee?->user?->name) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="text-sm font-semibold text-slate-700">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $employee?->user?->email) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('email')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="text-sm font-semibold text-slate-700">{{ $employee ? __('New Password') : __('Password') }}</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" @if(! $employee) required @endif class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('password')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="text-sm font-semibold text-slate-700">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @if(! $employee) required @endif class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Employment Details') }}</p>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="department_id" class="text-sm font-semibold text-slate-700">{{ __('Department') }}</label>
                    <select id="department_id" name="department_id" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        <option value="">{{ __('Choose department') }}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) old('department_id', $employee?->department_id) === (string) $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="job_title" class="text-sm font-semibold text-slate-700">{{ __('Job Title') }}</label>
                    <input id="job_title" name="job_title" type="text" value="{{ old('job_title', $employee?->job_title) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('job_title')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="status" class="text-sm font-semibold text-slate-700">{{ __('Status') }}</label>
                    <select id="status" name="status" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($statusValue === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="hire_date" class="text-sm font-semibold text-slate-700">{{ __('Hire Date') }}</label>
                    <input id="hire_date" name="hire_date" type="date" value="{{ $hireDate }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('hire_date')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Contact Information') }}</p>

            <div class="mt-6 space-y-5">
                <div>
                    <label for="phone" class="text-sm font-semibold text-slate-700">{{ __('Phone') }}</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $employee?->phone) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('phone')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="age" class="text-sm font-semibold text-slate-700">{{ __('Age') }}</label>
                    <input id="age" name="age" type="number" min="16" max="100" value="{{ old('age', $employee?->age) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('age')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Compensation') }}</p>

            <div class="mt-6 space-y-5">
                <div>
                    <label for="basic_salary" class="text-sm font-semibold text-slate-700">{{ __('Basic Salary') }}</label>
                    <input id="basic_salary" name="basic_salary" type="number" min="0" step="0.01" value="{{ old('basic_salary', $employee?->basic_salary ?? 0) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    @error('basic_salary')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="salary_type" class="text-sm font-semibold text-slate-700">{{ __('Salary Type') }}</label>
                    <select id="salary_type" name="salary_type" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        @foreach($salaryTypes as $value => $label)
                            <option value="{{ $value }}" @selected($salaryTypeValue === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('salary_type')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>
</div>

<div class="flex flex-col-reverse gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-end">
    <a href="{{ $cancelUrl }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
        {{ __('Cancel') }}
    </a>
    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
        {{ $submitLabel }}
    </button>
</div>
