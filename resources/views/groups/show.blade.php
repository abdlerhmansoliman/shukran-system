@extends('layouts.app')

@section('content')
@php
    $days = collect($group->days_of_week ?? [])
        ->map(fn ($day) => __(\Illuminate\Support\Str::headline($day)))
        ->implode(', ');
    $time = collect([
        $group->start_time ? substr((string) $group->start_time, 0, 5) : null,
        $group->end_time ? substr((string) $group->end_time, 0, 5) : null,
    ])->filter()->implode(' - ');
    $currentEnrollments = $group->groupEnrollments->whereIn('status', ['pending', 'ready', 'active']);
@endphp

<div class="bg-slate-100/70 py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Group Profile') }}</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ $group->name }}</h1>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a
                    href="{{ route('groups.edit', $group) }}"
                    class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                >
                    {{ __('Edit Group') }}
                </a>
                <a
                    href="{{ route('groups.index') }}"
                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    {{ __('Back to Groups') }}
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

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-700">
                {{ __('Please select at least one customer and try again.') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
            <div class="space-y-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Group Details') }}</p>

                    <div class="mt-5 space-y-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Status') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ __(\Illuminate\Support\Str::headline($group->status)) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Instructor') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $group->instructor?->name ?: __('Not specified') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Students') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">
                                {{ $currentEnrollments->count() }}@if($group->capacity) / {{ $group->capacity }}@endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Course Context') }}</p>
                    <div class="mt-5 space-y-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Level') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $group->level?->name ?: __('Not specified') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Category') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $group->category?->name ?: __('Not specified') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Parent Category') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $group->category?->parent?->name ?: __('Not specified') }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Schedule') }}</p>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Days') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $days ?: __('Days not specified') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Time') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $time ?: __('Time not specified') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('Start Date') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $group->start_date?->format('M d, Y') ?: __('Not scheduled') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">{{ __('End Date') }}</p>
                            <p class="mt-2 text-base font-semibold text-slate-900">{{ $group->end_date?->format('M d, Y') ?: __('Open-ended') }}</p>
                        </div>
                    </div>
                </div>

                @if($canAddCustomers)
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Add Customers') }}</p>

                        <form method="POST" action="{{ route('groups.customers.store', $group) }}" class="mt-5 space-y-4">
                            @csrf

                            <div>
                                <label for="customer-search" class="text-sm font-semibold text-slate-700">{{ __('Customers') }}</label>
                                <input
                                    id="customer-search"
                                    type="search"
                                    placeholder="{{ __('Search customers...') }}"
                                    class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10"
                                    data-customer-search
                                >

                                <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                                    <table class="min-w-full divide-y divide-slate-200">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th class="w-12 px-4 py-3 text-left">
                                                    <input type="checkbox" class="group-day-checkbox" data-customer-select-all aria-label="{{ __('Select visible customers') }}">
                                                </th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Customer') }}</th>
                                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Phone') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 bg-white" data-customer-table-body>
                                            @forelse($availableCustomers as $customer)
                                                @php
                                                    $name = trim($customer->first_name . ' ' . $customer->last_name);
                                                    $searchText = \Illuminate\Support\Str::lower($name.' '.$customer->phone);
                                                @endphp
                                                <tr data-customer-row data-search-text="{{ $searchText }}">
                                                    <td class="px-4 py-3">
                                                        <input
                                                            type="checkbox"
                                                            name="customer_ids[]"
                                                            value="{{ $customer->id }}"
                                                            class="group-day-checkbox"
                                                            data-customer-checkbox
                                                        >
                                                    </td>
                                                    <td class="px-4 py-3 text-sm font-semibold text-slate-900">
                                                        <div class="flex items-center gap-2">
                                                            {{ $name ?: __('Unnamed customer') }}
                                                            @if($customer->has_rejected)
                                                                <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-600/10" title="{{ __('Previously rejected a group offer') }}">
                                                                    {{ __('Previously Rejected') }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 text-sm font-medium text-slate-600">{{ $customer->phone ?: __('Not specified') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="px-4 py-5 text-sm text-slate-500">{{ __('No available customers') }}</td>
                                                </tr>
                                            @endforelse
                                            <tr class="hidden" data-customer-empty-row>
                                                <td colspan="3" class="px-4 py-5 text-sm text-slate-500">{{ __('No matching customers found') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                @error('customer_ids')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                @error('customer_ids.*')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </div>

                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                                {{ __('Add Selected Customers') }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Group Customers') }}</p>
                    <span class="text-sm font-medium text-slate-500">{{ trans_choice('{0} No customers|{1} :count customer|[2,*] :count customers', $group->groupEnrollments->count(), ['count' => $group->groupEnrollments->count()]) }}</span>
                </div>

                @if($group->groupEnrollments->isEmpty())
                    <div class="mt-5 rounded-3xl bg-slate-50 p-5">
                        <p class="text-sm leading-7 text-slate-600">{{ __('This group does not have customers yet.') }}</p>
                    </div>
                @else
                    <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Customer') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Package') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Joined') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach($group->groupEnrollments as $enrollment)
                                    @php
                                        $customer = $enrollment->customer;
                                        $name = trim(($customer?->first_name ?? '') . ' ' . ($customer?->last_name ?? ''));
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-4">
                                            @if($customer)
                                                <a href="{{ route('customers.show', $customer) }}" class="font-semibold text-slate-900">{{ $name ?: __('Unnamed customer') }}</a>
                                                <div class="mt-1 text-sm text-slate-500">{{ $customer->phone }}</div>
                                            @else
                                                <span class="text-sm text-slate-400">{{ __('Unknown customer') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-sm font-medium text-slate-700">
                                            {{ $enrollment->customerPackage?->package?->name ?: __('Not specified') }}
                                        </td>
                                        <td class="px-4 py-4 text-sm font-medium text-slate-700">
                                            {{ __(\Illuminate\Support\Str::headline($enrollment->status)) }}
                                        </td>
                                        <td class="px-4 py-4 text-sm font-medium text-slate-700">
                                            {{ $enrollment->joined_at?->format('M d, Y') ?: __('Not specified') }}
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <div class="flex flex-wrap justify-end gap-2">
                                                @if($group->status === 'planned' && $enrollment->status === 'pending')
                                                    <form method="POST" action="{{ route('groups.customers.update', [$group, $enrollment]) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="ready">
                                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">
                                                            {{ __('Confirm Customer') }}
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="{{ route('groups.customers.update', [$group, $enrollment]) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50">
                                                            {{ __('Reject Offer') }}
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($group->status === 'planned' && $enrollment->status === 'ready')
                                                    <form method="POST" action="{{ route('groups.customers.update', [$group, $enrollment]) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="pending">
                                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-white px-3 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-50">
                                                            {{ __('Undo Confirmation') }}
                                                        </button>
                                                    </form>
                                                @endif

                                                @if($group->status === 'planned' && in_array($enrollment->status, ['pending', 'ready'], true))
                                                    <form method="POST" action="{{ route('groups.customers.update', [$group, $enrollment]) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="cancelled">
                                                        <button
                                                            type="submit"
                                                            onclick="return confirm(@js(__('Cancel this enrollment?')))"
                                                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                                                        >
                                                            {{ __('Cancel') }}
                                                        </button>
                                                    </form>
                                                @endif

                                                <form method="POST" action="{{ route('groups.customers.destroy', [$group, $enrollment]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        onclick="return confirm(@js(__('Remove this customer from the group?')))"
                                                        class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50"
                                                    >
                                                        {{ __('Remove') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        (() => {
            const searchInput = document.querySelector('[data-customer-search]');
            const rows = Array.from(document.querySelectorAll('[data-customer-row]'));
            const emptyRow = document.querySelector('[data-customer-empty-row]');
            const selectAll = document.querySelector('[data-customer-select-all]');

            const visibleRows = () => rows.filter((row) => ! row.classList.contains('hidden'));

            const syncSelectAll = () => {
                if (! selectAll) {
                    return;
                }

                const visible = visibleRows();
                const visibleCheckboxes = visible
                    .map((row) => row.querySelector('[data-customer-checkbox]'))
                    .filter(Boolean);

                selectAll.checked = visibleCheckboxes.length > 0 && visibleCheckboxes.every((checkbox) => checkbox.checked);
                selectAll.indeterminate = visibleCheckboxes.some((checkbox) => checkbox.checked) && ! selectAll.checked;
            };

            const filterRows = () => {
                const term = (searchInput?.value || '').trim().toLowerCase();
                let visibleCount = 0;

                rows.forEach((row) => {
                    const matches = row.dataset.searchText.includes(term);
                    row.classList.toggle('hidden', ! matches);
                    visibleCount += matches ? 1 : 0;
                });

                emptyRow?.classList.toggle('hidden', rows.length === 0 || visibleCount !== 0);
                syncSelectAll();
            };

            searchInput?.addEventListener('input', filterRows);

            selectAll?.addEventListener('change', () => {
                visibleRows().forEach((row) => {
                    const checkbox = row.querySelector('[data-customer-checkbox]');

                    if (checkbox) {
                        checkbox.checked = selectAll.checked;
                    }
                });

                syncSelectAll();
            });

            rows.forEach((row) => {
                row.querySelector('[data-customer-checkbox]')?.addEventListener('change', syncSelectAll);
            });

            filterRows();
        })();
    </script>
@endpush
