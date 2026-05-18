@php
    $statusValue = old('status', $discount?->status ?? 'active');
    $typeValue = old('type', $discount?->type ?? 'percentage');
@endphp

<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Discount Details') }}</p>

    <div class="mt-6 grid gap-5 md:grid-cols-2">
        <div>
            <label for="name" class="text-sm font-semibold text-slate-700">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" value="{{ old('name', $discount?->name) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
            @error('name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="status" class="text-sm font-semibold text-slate-700">{{ __('Status') }}</label>
            <select id="status" name="status" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                <option value="active" @selected($statusValue === 'active')>{{ __('Active') }}</option>
                <option value="inactive" @selected($statusValue === 'inactive')>{{ __('Inactive') }}</option>
            </select>
            @error('status')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="type" class="text-sm font-semibold text-slate-700">{{ __('Type') }}</label>
            <select id="type" name="type" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                <option value="percentage" @selected($typeValue === 'percentage')>{{ __('Percentage') }}</option>
                <option value="fixed" @selected($typeValue === 'fixed')>{{ __('Fixed Amount') }}</option>
            </select>
            @error('type')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="amount" class="text-sm font-semibold text-slate-700">{{ __('Amount') }}</label>
            <input id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount', $discount?->amount ?? 0) }}" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
            @error('amount')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="flex flex-col-reverse gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-end mt-6">
    <a href="{{ $cancelUrl }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
        {{ __('Cancel') }}
    </a>
    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
        {{ $submitLabel }}
    </button>
</div>
