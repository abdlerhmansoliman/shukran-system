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

            <a
                href="{{ route('customers.create') }}"
                class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
            >
                {{ __('Add Customer') }}
            </a>
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

        <div class="mb-4 flex justify-end">
            <label for="customer-status-filter" class="sr-only">{{ __('Customer Status') }}</label>
            <select id="customer-status-filter" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm outline-none transition focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 sm:w-48">
                <option value="">{{ __('All statuses') }}</option>
                <option value="active">{{ __('Active') }}</option>
                <option value="inactive">{{ __('Inactive') }}</option>
            </select>
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
            const statusFilter = document.getElementById('customer-status-filter');

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

            window.jQuery?.(() => {
                const table = window.jQuery('#customer-table').DataTable();

                statusFilter?.addEventListener('change', () => {
                    table.column(4).search(statusFilter.value).draw();
                });

                window.jQuery('#customer-table').on('draw.dt', syncControls);
            });

            syncControls();
        })();
    </script>
@endpush
