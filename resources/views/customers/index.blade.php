@extends('layouts.app')

@section('content')
<div class="bg-slate-100/70 py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Customer Management') }}</p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ __('Customers') }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ __('Track contacts, monitor account status, and keep your customer pipeline organized from one place.') }}</p>
            </div>

            @can('create customers')
                <a
                    href="{{ route('customers.create') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                >
                    {{ __('Add Customer') }}
                </a>
            @endcan
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-700">
                {{ __('Please select a group and at least one customer.') }}
            </div>
        @endif

        <form
            id="bulk-group-form"
            method="POST"
            action="{{ route('customers.group-enrollments.store') }}"
            class="mb-6 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm"
        >
            @csrf
            <div id="bulk-customer-inputs"></div>

            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="grid flex-1 gap-4 md:grid-cols-[1fr_auto] md:items-end">
                    <div>
                        <label for="group_id" class="text-sm font-semibold text-slate-700">{{ __('Group') }}</label>
                        <select id="group_id" name="group_id" required class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10">
                            <option value="">{{ __('Choose group') }}</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" @selected((string) old('group_id') === (string) $group->id)>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('group_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm font-medium text-slate-600">
                        <span id="selected-customers-count">0</span> {{ __('selected') }}
                    </div>
                </div>

                <button
                    id="bulk-group-submit"
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300"
                    disabled
                >
                    {{ __('Add to Group') }}
                </button>
            </div>
        </form>

        <div class="mb-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-end gap-4">
                {{-- Date From --}}
                <div class="w-full sm:w-auto">
                    <label for="filter_from_date" class="text-sm font-semibold text-slate-700">{{ __('From Date') }}</label>
                    <input
                        type="date"
                        id="filter_from_date"
                        class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10"
                    >
                </div>

                {{-- Date To --}}
                <div class="w-full sm:w-auto">
                    <label for="filter_to_date" class="text-sm font-semibold text-slate-700">{{ __('To Date') }}</label>
                    <input
                        type="date"
                        id="filter_to_date"
                        class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10"
                    >
                </div>

                {{-- Status --}}
                <div class="w-full sm:w-auto">
                    <label for="filter_status" class="text-sm font-semibold text-slate-700">{{ __('Status') }}</label>
                    <select
                        id="filter_status"
                        class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10"
                    >
                        <option value="">{{ __('All statuses') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                    </select>
                </div>

                {{-- Level --}}
                <div class="w-full sm:w-auto">
                    <label for="filter_level" class="text-sm font-semibold text-slate-700">{{ __('Level') }}</label>
                    <select
                        id="filter_level"
                        class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10"
                    >
                        <option value="">{{ __('All levels') }}</option>
                        @foreach($levels ?? [] as $level)
                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Category --}}
                <div class="w-full sm:w-auto">
                    <label for="filter_category" class="text-sm font-semibold text-slate-700">{{ __('Category') }}</label>
                    <select
                        id="filter_category"
                        class="mt-2 block w-full rounded-xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-slate-900 focus:ring-slate-900/10"
                    >
                        <option value="">{{ __('All categories') }}</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @foreach($category->children as $child)
                                <option value="{{ $child->id }}">-- {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                {{-- Actions --}}
                <div class="flex w-full items-center gap-4 sm:w-auto">
                    <button
                        type="button"
                        id="apply-filters"
                        class="inline-flex flex-1 items-center justify-center rounded-xl bg-slate-900 px-5 py-[0.6rem] text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 sm:flex-none"
                    >
                        {{ __('Filter') }}
                    </button>

                    <button
                        type="button"
                        id="reset-filters"
                        class="text-sm font-medium text-slate-500 transition hover:text-slate-700"
                    >
                        {{ __('Reset') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            {!! $dataTable->table(['class' => 'customer-table min-w-full divide-y divide-slate-200'], true) !!}
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}
    <script>
        (() => {
            const selectedCustomers = new Set();
            const form = document.getElementById('bulk-group-form');
            const hiddenInputs = document.getElementById('bulk-customer-inputs');
            const selectedCount = document.getElementById('selected-customers-count');
            const submitButton = document.getElementById('bulk-group-submit');

            // Filter Elements
            const filterFromDate = document.getElementById('filter_from_date');
            const filterToDate = document.getElementById('filter_to_date');
            const filterStatus = document.getElementById('filter_status');
            const filterLevel = document.getElementById('filter_level');
            const filterCategory = document.getElementById('filter_category');
            const applyBtn = document.getElementById('apply-filters');
            const resetBtn = document.getElementById('reset-filters');

            const syncControls = () => {
                if (selectedCount) {
                    selectedCount.textContent = selectedCustomers.size;
                }

                if (submitButton) {
                    submitButton.disabled = selectedCustomers.size === 0;
                }

                document.querySelectorAll('[data-customer-checkbox]').forEach((checkbox) => {
                    checkbox.checked = selectedCustomers.has(checkbox.value);
                });

                document.querySelectorAll('[data-customer-select-all]').forEach((checkbox) => {
                    const visibleCheckboxes = Array.from(document.querySelectorAll('[data-customer-checkbox]'));
                    checkbox.checked = visibleCheckboxes.length > 0 && visibleCheckboxes.every((item) => item.checked);
                });
            };

            document.addEventListener('change', (event) => {
                const target = event.target;

                if (target.matches('[data-customer-checkbox]')) {
                    if (target.checked) {
                        selectedCustomers.add(target.value);
                    } else {
                        selectedCustomers.delete(target.value);
                    }

                    syncControls();
                }

                if (target.matches('[data-customer-select-all]')) {
                    document.querySelectorAll('[data-customer-checkbox]').forEach((checkbox) => {
                        checkbox.checked = target.checked;

                        if (target.checked) {
                            selectedCustomers.add(checkbox.value);
                        } else {
                            selectedCustomers.delete(checkbox.value);
                        }
                    });

                    syncControls();
                }
            });

            form?.addEventListener('submit', (event) => {
                if (selectedCustomers.size === 0) {
                    event.preventDefault();
                    return;
                }

                hiddenInputs.innerHTML = '';

                selectedCustomers.forEach((customerId) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'customer_ids[]';
                    input.value = customerId;
                    hiddenInputs.appendChild(input);
                });
            });

            const initTableEvents = () => {
                if (window.jQuery && window.LaravelDataTables && window.LaravelDataTables['customer-table']) {
                    const $table = window.jQuery('#customer-table');
                    
                    $table.on('preXhr.dt', function (e, settings, data) {
                        data.filter_status = filterStatus?.value || '';
                        data.from_date = filterFromDate?.value || '';
                        data.to_date = filterToDate?.value || '';
                        data.level_id = filterLevel?.value || '';
                        data.category_id = filterCategory?.value || '';
                    });

                    applyBtn?.addEventListener('click', () => {
                        window.LaravelDataTables['customer-table'].ajax.reload();
                    });

                    resetBtn?.addEventListener('click', () => {
                        if (filterFromDate) filterFromDate.value = '';
                        if (filterToDate) filterToDate.value = '';
                        if (filterStatus) filterStatus.value = '';
                        if (filterLevel) filterLevel.value = '';
                        if (filterCategory) filterCategory.value = '';
                        window.LaravelDataTables['customer-table'].ajax.reload();
                    });

                    $table.on('draw.dt', syncControls);
                } else {
                    setTimeout(initTableEvents, 100);
                }
            };

            initTableEvents();

            syncControls();
        })();
    </script>
@endpush
