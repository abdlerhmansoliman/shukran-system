@extends('layouts.app')

@section('content')
@php
    $fullName = trim($customer->first_name . ' ' . $customer->last_name);
    $initials = \Illuminate\Support\Str::upper(
        \Illuminate\Support\Str::substr($customer->first_name ?? '', 0, 1) .
        \Illuminate\Support\Str::substr($customer->last_name ?? '', 0, 1)
    );
    $payments = $customer->payments->sortByDesc('paid_at')->values();
    $walletTopUps = $payments->filter(fn ($p) => $p->direction === 'incoming' && ! $p->customer_package_id && ! $p->payroll_id);
    $subscriptionPayments = $payments->filter(fn ($p) => $p->customer_package_id !== null);
    $activeSubscriptions = $customer->customerPackages;
@endphp

<div class="bg-slate-100/70 py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Customer Wallet') }}</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">
                    {{ $fullName ?: __('Unnamed customer') }}
                </h1>
                <p class="mt-2 text-sm text-slate-500">{{ __('View wallet balance, top-up history, and outstanding installments.') }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    x-data
                    x-on:click="$dispatch('open-modal', 'wallet-top-up-page')"
                    class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                >
                    {{ __('Top Up Wallet') }}
                </button>
                <a
                    href="{{ route('customers.show', $customer) }}"
                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    {{ __('Back to Customer') }}
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

        {{-- Wallet Summary Cards --}}
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Wallet Balance') }}</p>
                <p class="mt-3 text-2xl font-semibold {{ (float) $customer->wallet_balance > 0 ? 'text-emerald-700' : 'text-slate-900' }}">
                    {{ number_format((float) $customer->wallet_balance, 2) }}
                </p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Total Top-Ups') }}</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900">
                    {{ number_format((float) $walletTopUps->where('status', 'completed')->sum('amount'), 2) }}
                </p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Total Paid') }}</p>
                <p class="mt-3 text-2xl font-semibold text-slate-900">
                    {{ number_format(
                        (float) $subscriptionPayments->where('status', 'completed')->where('direction', 'incoming')->sum('amount') -
                        (float) $subscriptionPayments->where('status', 'completed')->where('direction', 'outgoing')->sum('amount'),
                        2
                    ) }}
                </p>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Outstanding Balance') }}</p>
                <p class="mt-3 text-2xl font-semibold {{ (float) $activeSubscriptions->sum('remaining_amount') > 0 ? 'text-rose-700' : 'text-slate-900' }}">
                    {{ number_format((float) $activeSubscriptions->sum('remaining_amount'), 2) }}
                </p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="space-y-6">
                {{-- Outstanding Installments --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Outstanding Installments') }}</p>
                        <span class="text-sm font-medium text-slate-500">{{ $activeSubscriptions->count() }}</span>
                    </div>

                    @if($activeSubscriptions->isEmpty())
                        <div class="mt-5 rounded-3xl bg-slate-50 p-5">
                            <p class="text-sm leading-7 text-slate-600">{{ __('No outstanding installments for this customer.') }}</p>
                        </div>
                    @else
                        <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                            <div class="grid grid-cols-[minmax(0,1fr)_6rem_7rem_7rem_auto] gap-3 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-400 max-lg:hidden">
                                <span>{{ __('Subscription') }}</span>
                                <span>{{ __('Total') }}</span>
                                <span>{{ __('Paid') }}</span>
                                <span>{{ __('Remaining') }}</span>
                                <span class="text-right">{{ __('Action') }}</span>
                            </div>

                            @foreach($activeSubscriptions as $subscription)
                                @php
                                    $paidPercent = (float) $subscription->final_price > 0
                                        ? round(((float) $subscription->paid_amount / (float) $subscription->final_price) * 100)
                                        : 0;
                                @endphp
                                <div class="grid gap-3 border-t border-slate-200 px-4 py-4 text-sm first:border-t-0 lg:grid-cols-[minmax(0,1fr)_6rem_7rem_7rem_auto] lg:items-center">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $subscription->package?->name ?: __('Unknown package') }}</p>
                                        <div class="mt-2">
                                            <div class="flex items-center gap-2">
                                                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                                                    <div class="h-full rounded-full {{ $paidPercent >= 100 ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ min($paidPercent, 100) }}%"></div>
                                                </div>
                                                <span class="text-xs font-medium text-slate-500">{{ $paidPercent }}%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="font-semibold text-slate-900">
                                        <span class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400 lg:hidden">{{ __('Total') }}: </span>
                                        {{ number_format((float) $subscription->final_price, 2) }}
                                    </p>

                                    <p class="font-semibold text-emerald-700">
                                        <span class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400 lg:hidden">{{ __('Paid') }}: </span>
                                        {{ number_format((float) $subscription->paid_amount, 2) }}
                                    </p>

                                    <p class="font-semibold text-rose-700">
                                        <span class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400 lg:hidden">{{ __('Remaining') }}: </span>
                                        {{ number_format((float) $subscription->remaining_amount, 2) }}
                                    </p>

                                    <div class="flex justify-start lg:justify-end">
                                        <a
                                            href="{{ route('customers.payments.create', $customer) }}"
                                            class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800"
                                        >
                                            {{ __('Pay Installment') }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Full Payment History --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Payment History') }}</p>
                        <span class="text-sm font-medium text-slate-500">{{ $payments->count() }}</span>
                    </div>

                    @if($payments->isEmpty())
                        <div class="mt-5 rounded-3xl bg-slate-50 p-5">
                            <p class="text-sm leading-7 text-slate-600">{{ __('No payment records are available yet.') }}</p>
                        </div>
                    @else
                        <div class="mt-5 space-y-3">
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
                                    $purpose = $payment->payroll_id
                                        ? __('Payroll Payment')
                                        : ($payment->customer_package_id
                                            ? ($payment->direction === 'outgoing' && $payment->method === \App\Models\Payment::METHOD_WALLET_BALANCE
                                                ? __('Subscription Refund')
                                                : __('Subscription Payment'))
                                            : __('Wallet Top Up'));
                                    $methodLabel = match ($payment->method) {
                                        \App\Models\Payment::METHOD_WALLET_BALANCE => __('Wallet Balance'),
                                        default => $payment->paymentMethod?->name ?: $payment->method ?: __('Not specified'),
                                    };
                                @endphp
                                <div class="rounded-2xl border border-slate-200 p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="text-lg font-semibold text-slate-900">{{ number_format((float) $payment->amount, 2) }}</p>
                                            <p class="mt-1 text-sm text-slate-500">{{ $payment->paid_at?->format('M d, Y') ?: __('Not specified') }}</p>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $directionClasses }}">
                                                {{ __(\Illuminate\Support\Str::headline($payment->direction)) }}
                                            </span>
                                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses }}">
                                                {{ __(\Illuminate\Support\Str::headline($payment->status)) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Purpose') }}</p>
                                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $purpose }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Method') }}</p>
                                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $methodLabel }}</p>
                                        </div>
                                        @if($payment->customerPackage)
                                            <div>
                                                <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Subscription') }}</p>
                                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $payment->customerPackage->package?->name ?: __('Unknown package') }}</p>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Recorded By') }}</p>
                                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $payment->creator?->name ?: __('System / Unknown') }}</p>
                                        </div>
                                    </div>

                                    @if($payment->notes)
                                        <p class="mt-4 text-sm leading-6 text-slate-600">{{ $payment->notes }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                {{-- Quick Info --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Customer Info') }}</p>
                    <div class="mt-5 flex items-center gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-900 text-xl font-semibold text-white shadow-sm">
                            {{ $initials ?: 'NA' }}
                        </div>
                        <div>
                            <p class="text-lg font-semibold text-slate-900">{{ $fullName ?: __('Unnamed customer') }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $customer->phone ?: __('No phone') }}</p>
                        </div>
                    </div>
                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Email') }}</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $customer->email ?: __('No email provided') }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Status') }}</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">
                                {{ $customer->status instanceof \App\Enums\CustomerStatus ? $customer->status->label() : __(\Illuminate\Support\Str::headline((string) $customer->status)) }}
                            </p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Active Subscriptions') }}</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $activeSubscriptions->count() }}</p>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Quick Actions') }}</p>
                    <div class="mt-5 space-y-3">
                        <button
                            type="button"
                            x-data
                            x-on:click="$dispatch('open-modal', 'wallet-top-up-page')"
                            class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                            </svg>
                            {{ __('Top Up Wallet') }}
                        </button>
                        <a
                            href="{{ route('customers.payments.create', $customer) }}"
                            class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a5 5 0 00-10 0v2M5 12h14l1 9H4l1-9z" />
                            </svg>
                            {{ __('Record Payment') }}
                        </a>
                        <a
                            href="{{ route('customers.edit', $customer) }}"
                            class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ __('Edit Customer') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Top Up Modal --}}
<x-modal name="wallet-top-up-page" :show="false" maxWidth="4xl" focusable>
    <form method="POST" action="{{ route('customers.wallet.top-ups.store', $customer) }}" class="p-10 sm:p-12">
        @csrf
        
        <div class="mx-auto max-w-2xl">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Top Up Wallet') }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ __('Add funds to this customer wallet.') }}</p>
            </div>

            <div class="mb-5 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-5 py-4">
                <p class="text-sm font-medium text-slate-500">{{ __('Current Balance') }}</p>
                <p class="text-xl font-bold text-slate-900">{{ number_format((float) $customer->wallet_balance, 2) }}</p>
            </div>

            <div class="space-y-5">
                <div>
                    <label for="wallet_page_amount" class="text-sm font-semibold text-slate-700">{{ __('Amount') }}</label>
                    <input id="wallet_page_amount" name="amount" type="number" min="0.01" step="0.01" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="wallet_page_paid_at" class="text-sm font-semibold text-slate-700">{{ __('Payment Date') }}</label>
                        <input id="wallet_page_paid_at" name="paid_at" type="date" value="{{ now()->toDateString() }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    </div>

                    <div>
                        <label for="wallet_page_status" class="text-sm font-semibold text-slate-700">{{ __('Status') }}</label>
                        <select id="wallet_page_status" name="status" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                            @foreach(['completed' => __('Completed'), 'pending' => __('Pending'), 'cancelled' => __('Cancelled')] as $value => $label)
                                <option value="{{ $value }}" @selected($value === 'completed')>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="wallet_page_method" class="text-sm font-semibold text-slate-700">{{ __('Payment Method') }}</label>
                        <select id="wallet_page_method" name="payment_method_id" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                            <option value="">{{ __('Choose payment method') }}</option>
                            @foreach($paymentMethods as $paymentMethod)
                                <option value="{{ $paymentMethod->id }}">{{ $paymentMethod->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="wallet_page_reference" class="text-sm font-semibold text-slate-700">{{ __('Reference') }}</label>
                        <input id="wallet_page_reference" name="reference" type="text" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                    </div>
                </div>

                <div>
                    <label for="wallet_page_notes" class="text-sm font-semibold text-slate-700">{{ __('Notes') }}</label>
                    <textarea id="wallet_page_notes" name="notes" rows="2" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10"></textarea>
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    x-on:click="$dispatch('close-modal', 'wallet-top-up-page')"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    {{ __('Cancel') }}
                </button>
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                >
                    {{ __('Top Up Wallet') }}
                </button>
            </div>
        </div>
    </form>
</x-modal>
@endsection
