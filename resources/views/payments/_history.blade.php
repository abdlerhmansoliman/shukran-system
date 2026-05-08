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
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Method') }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $payment->paymentMethod?->name ?: $payment->method ?: __('Not specified') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Reference') }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $payment->reference ?: __('Not specified') }}</p>
                        </div>
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
