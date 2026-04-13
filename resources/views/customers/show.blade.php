@extends('layouts.app')

@section('content')
@php
    $fullName = trim($customer->first_name . ' ' . $customer->last_name);
    $initials = \Illuminate\Support\Str::upper(
        \Illuminate\Support\Str::substr($customer->first_name ?? '', 0, 1) .
        \Illuminate\Support\Str::substr($customer->last_name ?? '', 0, 1)
    );
@endphp

<div class="bg-slate-100/70 py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Customer Profile') }}</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
                    {{ $fullName ?: __('Unnamed customer') }}
                </h1>
                <p class="mt-2 text-sm text-slate-500">
                    {{ __('Detailed customer information, relationship context, and account activity in one place.') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a
                    href="{{ route('customers.index') }}"
                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    {{ __('Back to Customers') }}
                </a>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <div class="space-y-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-slate-900 text-2xl font-semibold text-white shadow-sm">
                                {{ $initials ?: 'NA' }}
                            </div>
                            <div>
                                <h2 class="text-2xl font-semibold text-slate-900">{{ $fullName ?: __('Unnamed customer') }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ $customer->email ?: __('No email provided') }}</p>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $customer->status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/20' }}">
                                        {{ \Illuminate\Support\Str::headline($customer->status) }}
                                    </span>

                                    @if($customer->source)
                                        <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 ring-1 ring-inset ring-sky-600/20">
                                            {{ \Illuminate\Support\Str::headline($customer->source) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                            <p class="font-medium text-slate-700">{{ __('Customer ID') }}</p>
                            <p class="mt-1">#{{ $customer->id }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Contact Information') }}</p>
                        <div class="mt-5 space-y-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Phone') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->phone ?: __('Not specified') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Email') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->email ?: __('No email provided') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Source') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->source ? \Illuminate\Support\Str::headline($customer->source) : __('Not specified') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Classification') }}</p>
                        <div class="mt-5 space-y-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Status') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ \Illuminate\Support\Str::headline($customer->status) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Level') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->level?->name ?: __('Not specified') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Category') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->category?->name ?: __('Not specified') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Notes') }}</p>
                    <div class="mt-5 rounded-3xl bg-slate-50 p-5">
                        <p class="text-sm leading-7 text-slate-600">
                            {{ $customer->notes ?: __('No notes available for this customer yet.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Account Timeline') }}</p>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Created At') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->created_at?->format('M d, Y h:i A') ?: __('Not specified') }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Last Updated') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->updated_at?->format('M d, Y h:i A') ?: __('Not specified') }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Ownership') }}</p>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Created By') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->creator?->name ?: __('System / Unknown') }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Customer Reference') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">CUS-{{ str_pad((string) $customer->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
