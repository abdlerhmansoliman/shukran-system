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
                    {{ __('View personal details, enrollment context, and package history for this customer.') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a
                    href="{{ route('customers.edit', $customer) }}"
                    class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                >
                    {{ __('Edit Customer') }}
                </a>
                <a
                    href="{{ route('customers.index') }}"
                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    {{ __('Back to Customers') }}
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="space-y-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-slate-900 text-2xl font-semibold text-white shadow-sm">
                                {{ $initials ?: 'NA' }}
                            </div>

                            <div>
                                <h2 class="text-2xl font-semibold text-slate-900">{{ $fullName ?: __('Unnamed customer') }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ $customer->email ?: __('No email provided') }}</p>

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $customer->status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/20' }}">
                                        {{ __(\Illuminate\Support\Str::headline($customer->status)) }}
                                    </span>

                                    @if($customer->source)
                                        <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 ring-1 ring-inset ring-sky-600/20">
                                            {{ __(\Illuminate\Support\Str::headline($customer->source)) }}
                                        </span>
                                    @endif

                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $customer->customer_type === 'old' ? 'bg-violet-50 text-violet-700 ring-violet-600/20' : 'bg-teal-50 text-teal-700 ring-teal-600/20' }}">
                                        {{ __(\Illuminate\Support\Str::headline($customer->customer_type ?? 'new')) }}
                                    </span>

                                    @if($customer->country?->name)
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                            {{ $customer->country->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                <p class="font-medium text-slate-700">{{ __('Customer ID') }}</p>
                                <p class="mt-1">#{{ $customer->id }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                <p class="font-medium text-slate-700">{{ __('Active Packages') }}</p>
                                <p class="mt-1">{{ $activePackagesCount }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                <p class="font-medium text-slate-700">{{ __('Total Package Value') }}</p>
                                <p class="mt-1">{{ number_format((float) $totalPackageValue, 2) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                <p class="font-medium text-slate-700">{{ __('Total Paid') }}</p>
                                <p class="mt-1">{{ number_format((float) $totalPaidAmount, 2) }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                <p class="font-medium text-slate-700">{{ __('Remaining') }}</p>
                                <p class="mt-1">{{ number_format((float) $totalRemainingAmount, 2) }}</p>
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
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->phone ?: __('Not specified') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Email') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->email ?: __('No email provided') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Address') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->address ?: __('Not specified') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Country') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">
                                    {{ $customer->country?->name ?: __('Not specified') }}
                                    @if($customer->country?->code)
                                        <span class="text-sm font-medium text-slate-500">({{ $customer->country->code }})</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Customer Details') }}</p>
                        <div class="mt-5 space-y-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Status') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ __(\Illuminate\Support\Str::headline($customer->status)) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Source') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->source ? __(\Illuminate\Support\Str::headline($customer->source)) : __('Not specified') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Customer Type') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ __(\Illuminate\Support\Str::headline($customer->customer_type ?? 'new')) }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Age') }}</p>
                                    <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->age ?: __('Not specified') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Gender') }}</p>
                                    <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->gender ? __(\Illuminate\Support\Str::headline($customer->gender)) : __('Not specified') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Classification') }}</p>
                        <div class="mt-5 space-y-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Level') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->level?->name ?: __('Not specified') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Category') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->category?->name ?: __('Not specified') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Parent Category') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->category?->parent?->name ?: __('Not specified') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Placement Context') }}</p>
                        <div class="mt-5 space-y-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Placement Month') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->placement_month?->format('F Y') ?: __('Not specified') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Tester') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->tester?->name ?: __('Not specified') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Old Instructor') }}</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->oldInstructor?->name ?: __('Not specified') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Ownership') }}</p>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Created By') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->creator?->name ?: __('System / Unknown') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Customer Reference') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">CUS-{{ str_pad((string) $customer->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Packages') }}</p>
                        <span class="text-sm font-medium text-slate-500">{{ trans_choice('{0} No packages|{1} :count package|[2,*] :count packages', $packages->count(), ['count' => $packages->count()]) }}</span>
                    </div>

                    @if($packages->isEmpty())
                        <div class="mt-5 rounded-3xl bg-slate-50 p-5">
                            <p class="text-sm leading-7 text-slate-600">{{ __('This customer has not been assigned any packages yet.') }}</p>
                        </div>
                    @else
                        <div class="mt-5 space-y-4">
                            @foreach($packages as $customerPackage)
                                <div class="rounded-3xl border border-slate-200 p-5">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="text-lg font-semibold text-slate-900">
                                                    {{ $customerPackage->package?->name ?: __('Unknown package') }}
                                                </h3>
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $customerPackage->status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : ($customerPackage->status === 'completed' ? 'bg-sky-50 text-sky-700 ring-sky-600/20' : 'bg-rose-50 text-rose-700 ring-rose-600/20') }}">
                                                    {{ __(\Illuminate\Support\Str::headline($customerPackage->status)) }}
                                                </span>
                                            </div>

                                            <p class="mt-2 text-sm text-slate-500">
                                                {{ __('Levels: :count', ['count' => $customerPackage->package?->levels_count ?? '—']) }}
                                            </p>
                                        </div>

                                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Price') }}</p>
                                                <p class="mt-2 text-base font-semibold text-slate-900">{{ number_format((float) $customerPackage->price, 2) }}</p>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Discount') }}</p>
                                                <p class="mt-2 text-base font-semibold text-slate-900">{{ number_format((float) $customerPackage->discount, 2) }}</p>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Final Price') }}</p>
                                                <p class="mt-2 text-base font-semibold text-slate-900">{{ number_format((float) $customerPackage->final_price, 2) }}</p>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Paid') }}</p>
                                                <p class="mt-2 text-base font-semibold text-slate-900">{{ number_format((float) $customerPackage->paid_amount, 2) }}</p>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Remaining') }}</p>
                                                <p class="mt-2 text-base font-semibold text-slate-900">{{ number_format((float) $customerPackage->remaining_amount, 2) }}</p>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Payment Status') }}</p>
                                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $customerPackage->payment_status ? __(\Illuminate\Support\Str::headline($customerPackage->payment_status)) : __('Not specified') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Start Date') }}</p>
                                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $customerPackage->start_date ? \Illuminate\Support\Carbon::parse($customerPackage->start_date)->format('M d, Y') : __('Not scheduled') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('End Date') }}</p>
                                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $customerPackage->end_date ? \Illuminate\Support\Carbon::parse($customerPackage->end_date)->format('M d, Y') : __('Open-ended') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Payment Date') }}</p>
                                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $customerPackage->payment_date ? \Illuminate\Support\Carbon::parse($customerPackage->payment_date)->format('M d, Y') : __('Not paid yet') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Assigned By') }}</p>
                                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $customerPackage->creator?->name ?: __('System / Unknown') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Assigned At') }}</p>
                                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $customerPackage->created_at?->format('M d, Y h:i A') ?: __('Not specified') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
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
                        <div class="rounded-2xl border border-slate-200 p-4">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Deleted At') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $customer->deleted_at?->format('M d, Y h:i A') ?: __('Active record') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
