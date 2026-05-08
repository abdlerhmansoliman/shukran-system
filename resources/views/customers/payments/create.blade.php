@extends('layouts.app')

@section('content')
@php
    $fullName = trim($customer->first_name . ' ' . $customer->last_name);
    $selectedPackageId = old('customer_package_id', $customerPackages->first()?->id);
    $selectedPackage = $customerPackages->firstWhere('id', (int) $selectedPackageId) ?: $customerPackages->first();
@endphp

<div class="bg-slate-100/70 py-10">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Customer Payment') }}</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ __('Record Customer Payment') }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ __('Record an incoming payment for :name.', ['name' => $fullName ?: __('Unnamed customer')]) }}</p>
            </div>

            <a
                href="{{ route('customers.show', $customer) }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
            >
                {{ __('Back to Customer') }}
            </a>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-700">
                {{ __('Please review the highlighted fields and try again.') }}
            </div>
        @endif

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Customer') }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $fullName ?: __('Unnamed customer') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Open Subscriptions') }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $customerPackages->count() }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Remaining') }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $customerPackages->sum('remaining_amount'), 2) }}</p>
                </div>
            </div>

            @if($customerPackages->isEmpty())
                <div class="rounded-3xl bg-slate-50 p-5">
                    <p class="text-sm leading-7 text-slate-600">{{ __('This customer does not have an active subscription with a remaining balance.') }}</p>
                </div>
            @else
                <form method="POST" action="{{ route('customers.payments.store', $customer) }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="customer_package_id" class="text-sm font-semibold text-slate-700">{{ __('Subscription') }}</label>
                        <select id="customer_package_id" name="customer_package_id" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                            @foreach($customerPackages as $customerPackage)
                                <option
                                    value="{{ $customerPackage->id }}"
                                    data-remaining="{{ number_format((float) $customerPackage->remaining_amount, 2, '.', '') }}"
                                    @selected((string) $selectedPackageId === (string) $customerPackage->id)
                                >
                                    {{ $customerPackage->package?->name ?: __('Unknown package') }} - {{ __('Remaining') }}: {{ number_format((float) $customerPackage->remaining_amount, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_package_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="payment_amount" class="text-sm font-semibold text-slate-700">{{ __('Amount') }}</label>
                        <input id="payment_amount" name="amount" type="number" min="0.01" step="0.01" value="{{ old('amount', $selectedPackage ? number_format((float) $selectedPackage->remaining_amount, 2, '.', '') : null) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                        @error('amount')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="payment_paid_at" class="text-sm font-semibold text-slate-700">{{ __('Payment Date') }}</label>
                            <input id="payment_paid_at" name="paid_at" type="date" value="{{ old('paid_at', now()->toDateString()) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                            @error('paid_at')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="payment_status" class="text-sm font-semibold text-slate-700">{{ __('Status') }}</label>
                            <select id="payment_status" name="status" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                                @foreach(['completed' => __('Completed'), 'pending' => __('Pending'), 'cancelled' => __('Cancelled')] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', 'completed') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="payment_method_id" class="text-sm font-semibold text-slate-700">{{ __('Payment Method') }}</label>
                            <select id="payment_method_id" name="payment_method_id" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                                <option value="">{{ __('Choose payment method') }}</option>
                                @foreach($paymentMethods as $paymentMethod)
                                    <option value="{{ $paymentMethod->id }}" @selected((string) old('payment_method_id') === (string) $paymentMethod->id)>{{ $paymentMethod->name }}</option>
                                @endforeach
                            </select>
                            @error('payment_method_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="payment_reference" class="text-sm font-semibold text-slate-700">{{ __('Reference') }}</label>
                            <input id="payment_reference" name="reference" type="text" value="{{ old('reference') }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                            @error('reference')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="payment_notes" class="text-sm font-semibold text-slate-700">{{ __('Notes') }}</label>
                        <textarea id="payment_notes" name="notes" rows="3" class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">{{ old('notes') }}</textarea>
                        @error('notes')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
                        <a href="{{ route('customers.show', $customer) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                            {{ __('Record Payment') }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        (() => {
            const packageSelect = document.getElementById('customer_package_id');
            const amountInput = document.getElementById('payment_amount');

            packageSelect?.addEventListener('change', () => {
                if (amountInput && packageSelect.selectedOptions[0]?.dataset.remaining) {
                    amountInput.value = packageSelect.selectedOptions[0].dataset.remaining;
                }
            });
        })();
    </script>
@endpush
