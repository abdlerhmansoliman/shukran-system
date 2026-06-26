@extends('layouts.app')

@section('content')
@php
    $fullName = trim($customer->first_name . ' ' . $customer->last_name);
    $initials = \Illuminate\Support\Str::upper(
        \Illuminate\Support\Str::substr($customer->first_name ?? '', 0, 1) .
        \Illuminate\Support\Str::substr($customer->last_name ?? '', 0, 1)
    );
    $packages = $customer->customerPackages->sortByDesc('created_at')->values();
    $activePackagesCount = $packages->where('status', 'active')->count();
    $totalPackageValue = $packages->sum('final_price');
    $totalPaidAmount = $packages->sum('paid_amount');
    $totalRemainingAmount = $packages->sum('remaining_amount');
    $groupEnrollments = $customer->groupEnrollments->sortByDesc('joined_at')->values();
    $payments = $customer->payments->sortByDesc('paid_at')->values();
@endphp

<div class="bg-slate-100/70 py-10 min-h-screen" x-data="{ activeTab: 'info' }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumbs / Top Navigation -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Customer Profile') }}</p>
            </div>
            <div>
                <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-wider text-slate-500 hover:text-slate-900 transition duration-150">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>{{ __('Back to Customers') }}</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        <!-- Hero Header Card -->
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm mb-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                
                <!-- Customer Main Info -->
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                    <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-3xl bg-slate-950 text-3xl font-extrabold text-white shadow-md">
                        {{ $initials ?: 'NA' }}
                    </div>

                    <div class="text-center sm:text-left">
                        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-955">{{ $fullName ?: __('Unnamed customer') }}</h2>
                        
                        <p class="mt-1.5 text-sm font-medium text-slate-500 flex items-center justify-center sm:justify-start gap-1">
                            <svg class="h-4 w-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>{{ $customer->email ?: __('No email provided') }}</span>
                        </p>

                        <!-- Tags / Badges -->
                        <div class="mt-4 flex flex-wrap items-center justify-center sm:justify-start gap-2">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $customer->status instanceof \App\Enums\CustomerStatus ? $customer->status->color() : 'bg-slate-100 text-slate-600 ring-slate-500/20' }}">
                                {{ $customer->status instanceof \App\Enums\CustomerStatus ? $customer->status->label() : __(\Illuminate\Support\Str::headline((string) $customer->status)) }}
                            </span>

                            @if($customer->source)
                                <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700 ring-1 ring-inset ring-sky-600/20">
                                    {{ __(\Illuminate\Support\Str::headline($customer->source)) }}
                                </span>
                            @endif

                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $customer->customer_type === 'old' ? 'bg-violet-50 text-violet-700 ring-violet-600/20' : 'bg-teal-50 text-teal-700 ring-teal-600/20' }}">
                                {{ __(\Illuminate\Support\Str::headline($customer->customer_type ?? 'new')) }}
                            </span>

                            @if($customer->keywords)
                                <span class="inline-flex items-center rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 ring-1 ring-inset ring-rose-600/20">
                                    {{ $customer->keywords->label() }}
                                </span>
                            @endif

                            @if($customer->country?->name)
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                    {{ $customer->country->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Primary Action Buttons -->
                <div class="flex flex-wrap items-center justify-center lg:justify-end gap-3 border-t border-slate-100 pt-5 lg:border-t-0 lg:pt-0">
                    <a
                        href="{{ route('customers.payments.create', $customer) }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-slate-955 px-4 py-3 text-sm font-bold text-white shadow-sm transition duration-150 hover:bg-slate-800"
                    >
                        {{ __('Record Payment') }}
                    </a>
                    <a
                        href="{{ route('customers.wallet.show', $customer) }}"
                        class="inline-flex items-center justify-center rounded-2xl bg-slate-955 px-4 py-3 text-sm font-bold text-white shadow-sm transition duration-150 hover:bg-slate-800"
                    >
                        {{ __('Wallet') }}
                    </a>
                    <a
                        href="{{ route('customers.edit', $customer) }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition duration-150 hover:bg-slate-50"
                    >
                        {{ __('Edit Customer') }}
                    </a>
                </div>

            </div>
        </div>

        <!-- Financial & Subscriptions Stats Row -->
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <!-- Wallet Balance -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Wallet Balance') }}</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format((float) $customer->wallet_balance, 2) }}</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-2xl text-emerald-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            
            <!-- Active Subscriptions -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Active Subscriptions') }}</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ $activePackagesCount }}</p>
                </div>
                <div class="p-3 bg-sky-50 rounded-2xl text-sky-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>

            <!-- Total Paid vs Total Value -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Total Paid') }} / {{ __('Total') }}</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">
                        {{ number_format((float) $totalPaidAmount, 2) }}
                        <span class="text-sm font-normal text-slate-400">/ {{ number_format((float) $totalPackageValue, 2) }}</span>
                    </p>
                </div>
                <div class="p-3 bg-violet-50 rounded-2xl text-violet-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>

            <!-- Remaining Balance -->
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Remaining') }}</p>
                    <p class="mt-2 text-2xl font-bold {{ $totalRemainingAmount > 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ number_format((float) $totalRemainingAmount, 2) }}</p>
                </div>
                <div class="p-3 {{ $totalRemainingAmount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-600' }} rounded-2xl">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Main Workspace: Full Width Workspace -->
        <div class="space-y-6">
                
                <!-- Tab Headers -->
                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <nav class="flex flex-wrap gap-2" aria-label="Tabs">
                        <button 
                            @click="activeTab = 'info'"
                            :class="activeTab === 'info' ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                            class="rounded-2xl px-5 py-3 text-sm font-semibold transition duration-150 cursor-pointer focus:outline-none"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>{{ __('Personal Info') }}</span>
                            </div>
                        </button>

                        <button 
                            @click="activeTab = 'subscriptions'"
                            :class="activeTab === 'subscriptions' ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                            class="rounded-2xl px-5 py-3 text-sm font-semibold transition duration-150 cursor-pointer focus:outline-none"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <span>{{ __('Subscriptions & Groups') }}</span>
                            </div>
                        </button>

                        <button 
                            @click="activeTab = 'payments'"
                            :class="activeTab === 'payments' ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                            class="rounded-2xl px-5 py-3 text-sm font-semibold transition duration-150 cursor-pointer focus:outline-none"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span>{{ __('Payment History') }}</span>
                            </div>
                        </button>

                        <button 
                            @click="activeTab = 'notes_feedback'"
                            :class="activeTab === 'notes_feedback' ? 'bg-slate-950 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                            class="rounded-2xl px-5 py-3 text-sm font-semibold transition duration-150 cursor-pointer focus:outline-none"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                                <span>{{ __('Notes & Feedback') }}</span>
                            </div>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content Sections -->
                
                <!-- Tab 1: Personal Info -->
                <div x-show="activeTab === 'info'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                    
                    <!-- Contact Details Card -->
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-slate-950 pb-4 border-b border-slate-100 flex items-center gap-2">
                            <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>{{ __('Contact Information') }}</span>
                        </h3>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2 md:grid-cols-3">
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Phone') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->phone ?: __('Not specified') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Second Phone') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->second_phone_number ?: __('Not specified') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Email') }}</p>
                                <p class="text-base font-bold text-slate-900 truncate" title="{{ $customer->email }}">{{ $customer->email ?: __('No email provided') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Country') }}</p>
                                <p class="text-base font-bold text-slate-900">
                                    {{ $customer->country?->name ?: __('Not specified') }}
                                    @if($customer->country?->code)
                                        <span class="text-sm font-normal text-slate-400">({{ $customer->country->code }})</span>
                                    @endif
                                </p>
                            </div>
                            <div class="space-y-1 sm:col-span-2">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Address') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->address ?: __('Not specified') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Demographic Card -->
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-slate-955 pb-4 border-b border-slate-100 flex items-center gap-2">
                            <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                            </svg>
                            <span>{{ __('Demographic & Professional Info') }}</span>
                        </h3>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2 md:grid-cols-3">
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Age') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->age ?: __('Not specified') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Job') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->job ?: __('Not specified') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('College') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->college ?: __('Not specified') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Gender') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->gender ? __(\Illuminate\Support\Str::headline($customer->gender)) : __('Not specified') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Personality') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->keywords ? $customer->keywords->label() : __('Not specified') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Classification Card -->
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-slate-955 pb-4 border-b border-slate-100 flex items-center gap-2">
                            <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span>{{ __('Classification') }}</span>
                        </h3>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2 md:grid-cols-4">
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Entry Level') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->entryLevel?->name ?: __('Not specified') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Current Level') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->currentLevel?->name ?: __('Not specified') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Category') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->category?->name ?: __('Not specified') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Parent Category') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->category?->parent?->name ?: __('Not specified') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Placement & Reference Context Card -->
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-slate-955 pb-4 border-b border-slate-100 flex items-center gap-2">
                            <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            <span>{{ __('Placement & Reference') }}</span>
                        </h3>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2 md:grid-cols-3">
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Placement Month') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->placement_month?->format('F Y') ?: __('Not specified') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Tester') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->tester?->name ?: __('Not specified') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Test Date') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->test_date?->format('M d, Y') ?: __('Not specified') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Progress Report') }}</p>
                                @if($customer->progress_report_link)
                                    <a href="{{ str_starts_with($customer->progress_report_link, 'http') ? $customer->progress_report_link : 'https://' . $customer->progress_report_link }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-base font-bold text-slate-900 hover:text-indigo-600 hover:underline">
                                        <span class="truncate max-w-[12rem]">{{ $customer->progress_report_link }}</span>
                                        <svg class="ml-1 h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                @else
                                    <p class="text-base font-bold text-slate-900">{{ __('Not specified') }}</p>
                                @endif
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Created By') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->creator?->name ?: __('System / Unknown') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Reference ID') }}</p>
                                <p class="text-base font-bold text-slate-900">CUS-{{ str_pad((string) $customer->id, 5, '0', STR_PAD_LEFT) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tab 2: Subscriptions & Groups -->
                <div x-show="activeTab === 'subscriptions'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                    
                    <!-- Agreed Details Card -->
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-slate-955 pb-4 border-b border-slate-100 flex items-center gap-2">
                            <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ __('Agreed Details (Expected Deal)') }}</span>
                        </h3>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2">
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Agreed Package') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->agreedPackage?->name ?: __('Not specified') }}</p>
                            </div>
                            <div class="space-y-1">
                                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ __('Agreed Amount') }}</p>
                                <p class="text-base font-bold text-slate-900">{{ $customer->agreed_amount !== null ? number_format((float) $customer->agreed_amount, 2) : __('Not specified') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Subscriptions Card -->
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <h3 class="text-lg font-bold text-slate-955 flex items-center gap-2">
                                <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span>{{ __('Subscriptions') }}</span>
                            </h3>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ trans_choice('{0} No subscriptions|{1} :count subscription|[2,*] :count subscriptions', $packages->count(), ['count' => $packages->count()]) }}</span>
                        </div>

                        @if($packages->isEmpty())
                            <div class="mt-6 rounded-2xl bg-slate-50 p-6 text-center">
                                <p class="text-sm text-slate-500 font-medium">{{ __('This customer does not have any subscriptions yet.') }}</p>
                            </div>
                        @else
                            <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-200 text-left">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Subscription') }}</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Status') }}</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Paid') }}</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Remaining') }}</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Started') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 bg-white">
                                            @foreach($packages as $customerPackage)
                                                @php
                                                    $statusClasses = match ($customerPackage->status) {
                                                        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                                        'completed' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
                                                        default => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                                                    };
                                                @endphp
                                                <tr class="hover:bg-slate-50/50 transition duration-150">
                                                    <td class="px-6 py-4">
                                                        <div class="text-sm font-bold text-slate-900">{{ $customerPackage->package?->name ?: __('Unknown package') }}</div>
                                                        <div class="text-xs text-slate-500 mt-0.5">
                                                            {{ __('Remaining Levels: :count', ['count' => $customerPackage->levels_count ?? '—']) }}
                                                            @if($customerPackage->discountTemplate)
                                                                <span class="ml-2 text-rose-600 font-bold">({{ $customerPackage->discountTemplate->name }} - {{ number_format((float)$customerPackage->discount, 2) }})</span>
                                                            @endif
                                                        </div>
                                                        @if($customerPackage->status === 'cancelled')
                                                            @php
                                                                $refundPayment = $customerPackage->payments->where('direction', 'outgoing')->first();
                                                            @endphp
                                                            @if($refundPayment && $refundPayment->notes)
                                                                <div class="mt-1.5 text-xs text-rose-600 font-bold bg-rose-50/50 rounded-lg px-2.5 py-1 inline-block">
                                                                    {{ $refundPayment->notes }}
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4">
                                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $statusClasses }}">
                                                            {{ __(\Illuminate\Support\Str::headline($customerPackage->status)) }}
                                                        </span>
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-slate-900">
                                                        {{ number_format((float) $customerPackage->paid_amount, 2) }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-slate-900">
                                                        {{ number_format((float) $customerPackage->remaining_amount, 2) }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-500">
                                                        {{ $customerPackage->start_date?->format('M d, Y') ?: __('Not scheduled') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                                    {{ __('Manage Subscriptions') }}
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Groups Card -->
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <h3 class="text-lg font-bold text-slate-955 flex items-center gap-2">
                                <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>{{ __('Groups Enrolled') }}</span>
                            </h3>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ trans_choice('{0} No groups|{1} :count group|[2,*] :count groups', $groupEnrollments->count(), ['count' => $groupEnrollments->count()]) }}</span>
                        </div>

                        @if($groupEnrollments->isEmpty())
                            <div class="mt-6 rounded-2xl bg-slate-50 p-6 text-center">
                                <p class="text-sm text-slate-500 font-medium">{{ __('This customer has not been added to any groups yet.') }}</p>
                            </div>
                        @else
                            <div class="mt-6 space-y-4">
                                @foreach($groupEnrollments as $enrollment)
                                    @php
                                        $group = $enrollment->group;
                                        $days = collect($group?->days_of_week ?? [])
                                            ->map(fn ($day) => __(\Illuminate\Support\Str::headline($day)))
                                            ->implode(', ');
                                        $time = collect([
                                            $group?->start_time ? substr((string) $group->start_time, 0, 5) : null,
                                            $group?->end_time ? substr((string) $group->end_time, 0, 5) : null,
                                        ])->filter()->implode(' - ');
                                    @endphp

                                    <div class="rounded-2xl border border-slate-200 p-5 bg-slate-50/50 hover:bg-slate-50 transition duration-150">
                                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    @if($group)
                                                        <a href="{{ route('groups.show', $group) }}" class="text-lg font-bold text-slate-900 hover:text-indigo-600 transition">
                                                            {{ $group->name }}
                                                        </a>
                                                    @else
                                                        <h3 class="text-lg font-bold text-slate-900">{{ __('Unknown group') }}</h3>
                                                    @endif
                                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 ring-inset {{ $enrollment->status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : ($enrollment->status === 'completed' ? 'bg-sky-50 text-sky-700 ring-sky-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/20') }}">
                                                        {{ __(\Illuminate\Support\Str::headline($enrollment->status)) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="grid gap-3 sm:grid-cols-3">
                                                <div class="rounded-xl bg-white border border-slate-200/60 px-3 py-2 text-xs">
                                                    <p class="font-bold text-slate-400 uppercase tracking-wider">{{ __('Instructor') }}</p>
                                                    <p class="mt-1 font-bold text-slate-900">{{ $group?->instructor?->name ?: __('Not specified') }}</p>
                                                </div>
                                                <div class="rounded-xl bg-white border border-slate-200/60 px-3 py-2 text-xs">
                                                    <p class="font-bold text-slate-400 uppercase tracking-wider">{{ __('Product Template') }}</p>
                                                    <p class="mt-1 font-bold text-slate-900">{{ $enrollment->customerPackage?->package?->name ?: $group?->package?->name ?? __('Not specified') }}</p>
                                                </div>
                                                <div class="rounded-xl bg-white border border-slate-200/60 px-3 py-2 text-xs">
                                                    <p class="font-bold text-slate-400 uppercase tracking-wider">{{ __('Joined') }}</p>
                                                    <p class="mt-1 font-bold text-slate-900">{{ $enrollment->joined_at?->format('M d, Y') ?: __('Not specified') }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 grid gap-4 grid-cols-3 border-t border-slate-200/60 pt-4">
                                            <div class="text-xs">
                                                <p class="font-bold text-slate-400 uppercase tracking-wider">{{ __('Level') }}</p>
                                                <p class="mt-1 font-bold text-slate-900">{{ $group?->level?->name ?: __('Not specified') }}</p>
                                            </div>
                                            <div class="text-xs">
                                                <p class="font-bold text-slate-400 uppercase tracking-wider">{{ __('Days') }}</p>
                                                <p class="mt-1 font-bold text-slate-900 truncate" title="{{ $days }}">{{ $days ?: __('Days not specified') }}</p>
                                            </div>
                                            <div class="text-xs">
                                                <p class="font-bold text-slate-400 uppercase tracking-wider">{{ __('Time') }}</p>
                                                <p class="mt-1 font-bold text-slate-900">{{ $time ?: __('Time not specified') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tab 3: Notes & Feedback -->
                <div x-show="activeTab === 'notes_feedback'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                    
                    <!-- Internal Notes -->
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-slate-955 pb-4 border-b border-slate-100 flex items-center gap-2">
                            <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>{{ __('Internal Notes') }}</span>
                        </h3>
                        <div class="mt-6 rounded-2xl bg-slate-50 p-5 min-h-[100px] border border-slate-100">
                            <p class="text-sm leading-7 text-slate-700 whitespace-pre-line font-medium">
                                {{ $customer->notes ?: __('No notes available for this customer yet.') }}
                            </p>
                        </div>
                    </div>

                    <!-- Feedback Timeline Card -->
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <h3 class="text-lg font-bold text-slate-955 flex items-center gap-2">
                                <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <span>{{ __('Feedback Timeline') }}</span>
                            </h3>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                {{ trans_choice('{0} No feedback|{1} 1 feedback entry|[2,*] :count feedback entries', $customer->feedbacks->count(), ['count' => $customer->feedbacks->count()]) }}
                            </span>
                        </div>

                        <!-- Add Feedback Form -->
                        <form action="{{ route('customers.feedbacks.store', $customer) }}" method="POST" class="mt-6 rounded-2xl border border-slate-200 bg-slate-50/50 p-5">
                            @csrf
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="feedback_level_id" class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('Associated Level') }}</label>
                                    <select id="feedback_level_id" name="level_id" class="mt-2 block w-full rounded-xl border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-slate-955 focus:ring-slate-955/10">
                                        <option value="">{{ __('General Feedback') }}</option>
                                        @foreach($levels as $level)
                                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label for="feedback_text" class="text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('Feedback Message') }}</label>
                                <textarea id="feedback_text" name="feedback" rows="3" required placeholder="{{ __('Type feedback here...') }}" class="mt-2 block w-full rounded-xl border-slate-300 bg-white text-sm text-slate-700 shadow-sm focus:border-slate-955 focus:ring-slate-955/10"></textarea>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-955 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition duration-150 hover:bg-slate-800 cursor-pointer">
                                    {{ __('Add Feedback') }}
                                </button>
                            </div>
                        </form>

                        <!-- Feedback Entries list -->
                        @if($customer->feedbacks->isEmpty())
                            <div class="mt-6 rounded-2xl bg-slate-50 p-6 text-center border border-dashed border-slate-200">
                                <p class="text-sm text-slate-500 font-medium">
                                    {{ __('No feedback has been recorded for this customer yet.') }}
                                </p>
                            </div>
                        @else
                            <div class="mt-8 relative border-l-2 border-slate-100 ml-4 pl-6 space-y-6">
                                @foreach($customer->feedbacks as $feedback)
                                    <div class="relative">
                                        <!-- Timeline circle marker -->
                                        <div class="absolute -left-[31px] top-1 bg-white border-2 border-slate-955 rounded-full h-4 w-4"></div>
                                        
                                        <div class="rounded-2xl border border-slate-200 p-5 bg-white shadow-sm hover:border-slate-300 hover:shadow transition duration-150">
                                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-50 pb-2.5 mb-2.5">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="font-bold text-slate-900">
                                                        {{ $feedback->creator?->name ?: __('System / Unknown') }}
                                                    </span>
                                                    @if($feedback->level)
                                                        <span class="inline-flex items-center rounded-full bg-slate-150 px-2.5 py-0.5 text-xs font-bold text-slate-800">
                                                            {{ $feedback->level->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <span class="text-xs text-slate-400 font-bold">
                                                    {{ $feedback->created_at->format('M d, Y h:i A') }}
                                                </span>
                                            </div>
                                            <p class="text-sm leading-6 text-slate-600 whitespace-pre-line font-medium">{{ $feedback->feedback }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tab 3: Payment History -->
                <div x-show="activeTab === 'payments'" class="space-y-6" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                            <h3 class="text-lg font-bold text-slate-955 flex items-center gap-2">
                                <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span>{{ __('Payment History') }}</span>
                            </h3>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                {{ trans_choice('{0} No payments|{1} 1 payment|[2,*] :count payments', $payments->count(), ['count' => $payments->count()]) }}
                            </span>
                        </div>

                        @if($payments->isEmpty())
                            <div class="rounded-2xl bg-slate-50 p-6 text-center">
                                <p class="text-sm text-slate-500 font-medium">{{ __('No payment records are available yet.') }}</p>
                            </div>
                        @else
                            <div class="overflow-hidden rounded-2xl border border-slate-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-200 text-left">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Amount') }}</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Type & Purpose') }}</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Method & Reference') }}</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Date') }}</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('Recorded By') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 bg-white">
                                            @foreach($payments as $payment)
                                                @php
                                                    $directionClasses = $payment->direction === 'incoming'
                                                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'
                                                        : 'bg-rose-50 text-rose-700 ring-rose-600/20';
                                                    $statusClasses = match ($payment->status) {
                                                        'completed' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
                                                        'cancelled' => 'bg-slate-100 text-slate-600 ring-slate-500/20',
                                                        default => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                    };
                                                    $purpose = $payment->payroll
                                                        ? __('Payroll Payment')
                                                        : ($payment->customerPackage
                                                            ? ($payment->direction === 'outgoing' && $payment->method === \App\Models\Payment::METHOD_WALLET_BALANCE
                                                                ? __('Subscription Refund')
                                                                : __('Subscription Payment'))
                                                            : __('Wallet Top Up'));
                                                    $methodLabel = match ($payment->method) {
                                                        \App\Models\Payment::METHOD_WALLET_BALANCE => __('Wallet Balance'),
                                                        default => $payment->paymentMethod?->name ?: $payment->method ?: __('Not specified'),
                                                    };
                                                @endphp
                                                <tr class="hover:bg-slate-50/50 transition duration-150">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-base font-extrabold text-slate-900">
                                                            {{ $payment->direction === 'incoming' ? '+' : '-' }}{{ number_format((float) $payment->amount, 2) }}
                                                        </div>
                                                        <div class="mt-1 flex gap-1.5">
                                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 ring-inset {{ $directionClasses }}">
                                                                {{ __(\Illuminate\Support\Str::headline($payment->direction)) }}
                                                            </span>
                                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ring-1 ring-inset {{ $statusClasses }}">
                                                                {{ __(\Illuminate\Support\Str::headline($payment->status)) }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="text-sm font-bold text-slate-900">{{ $purpose }}</div>
                                                        @if($payment->customerPackage?->package)
                                                            <div class="text-xs text-slate-500 mt-0.5">
                                                                {{ $payment->customerPackage->package->name }}
                                                            </div>
                                                        @endif
                                                        @if($payment->notes)
                                                            <div class="mt-1.5 text-xs text-slate-600 font-medium bg-slate-50 rounded-lg px-2.5 py-1 inline-block text-wrap max-w-xs">
                                                                {{ $payment->notes }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="text-sm font-bold text-slate-900">{{ $methodLabel }}</div>
                                                        <div class="text-xs text-slate-500 mt-0.5">{{ $payment->reference ?: __('No reference') }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-500">
                                                        {{ $payment->paid_at?->format('M d, Y h:i A') ?: __('Not specified') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900">
                                                        {{ $payment->creator?->name ?: __('System / Unknown') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

    </div>
</div>
@endsection
