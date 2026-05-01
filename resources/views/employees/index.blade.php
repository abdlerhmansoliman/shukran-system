@extends('layouts.app')

@section('content')
<div class="bg-slate-100/70 py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Employee Management') }}</p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ __('Employees') }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ __('Review team members, departments, salary types, and employment status from one organized table.') }}</p>
            </div>

            <a
                href="{{ route('employees.create') }}"
                class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
            >
                {{ __('Add Employee') }}
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            {!! $dataTable->table(['class' => 'employee-table min-w-full divide-y divide-slate-200'], true) !!}
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}
@endpush
