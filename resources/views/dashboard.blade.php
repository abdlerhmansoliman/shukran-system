<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-900">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $money = fn ($amount) => number_format((float) $amount, 2);
    @endphp

    <div class="space-y-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('System Overview') }}</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ __('Shukran Today') }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ __('A quick view of customers, subscriptions, groups, payments, and payroll.') }}</p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('customers.create') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                    {{ __('Add Customer') }}
                </a>
                <a href="{{ route('groups.create') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    {{ __('Create Group') }}
                </a>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('Total Customers') }}</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ number_format($stats['total_customers']) }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ __('Active: :count', ['count' => number_format($stats['active_customers'])]) }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('Active Subscriptions') }}</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ number_format($stats['active_subscriptions']) }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ __('Open balance: :amount', ['amount' => $money($stats['subscription_balance'])]) }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('Groups Running') }}</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ number_format($stats['active_groups']) }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ __('Planned: :count | Enrollments: :enrollments', ['count' => number_format($stats['planned_groups']), 'enrollments' => number_format($stats['active_enrollments'])]) }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('Payroll Drafts') }}</p>
                <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $money($stats['draft_payroll_total']) }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ __(':count draft payrolls this month', ['count' => number_format($stats['draft_payroll_count'])]) }}</p>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('Customer Payments This Month') }}</p>
                <p class="mt-3 text-3xl font-semibold text-emerald-700">{{ $money($stats['incoming_this_month']) }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('Salary Payments This Month') }}</p>
                <p class="mt-3 text-3xl font-semibold text-rose-700">{{ $money($stats['outgoing_this_month']) }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ __('Net Cash This Month') }}</p>
                <p class="mt-3 text-3xl font-semibold {{ $stats['net_cash_this_month'] >= 0 ? 'text-slate-900' : 'text-rose-700' }}">{{ $money($stats['net_cash_this_month']) }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Open Subscription Balances') }}</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ __('Customers to follow up') }}</h3>
                    </div>
                    <a href="{{ route('customers.index') }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900">{{ __('View all') }}</a>
                </div>

                <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Customer') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Subscription') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Remaining') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse($unpaidSubscriptions as $subscription)
                                @php
                                    $customer = $subscription->customer;
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
                                    <td class="px-4 py-4 text-sm font-medium text-slate-700">{{ $subscription->package?->name ?: __('Unknown package') }}</td>
                                    <td class="px-4 py-4 text-right text-sm font-semibold text-slate-900">{{ $money($subscription->remaining_amount) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-5 text-sm text-slate-500">{{ __('No open subscription balances.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Group Activity') }}</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ __('Planned and active groups') }}</h3>
                    </div>
                    <a href="{{ route('groups.index') }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900">{{ __('View all') }}</a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($currentGroups as $group)
                        <a href="{{ route('groups.show', $group) }}" class="block rounded-2xl border border-slate-200 px-4 py-4 transition hover:border-slate-300 hover:bg-slate-50">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $group->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $group->category?->parent ? $group->category->parent->name . ' / ' : '' }}{{ $group->category?->name ?: __('No category') }}</p>
                                </div>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ __(\Illuminate\Support\Str::headline($group->status)) }}</span>
                            </div>
                            <p class="mt-3 text-sm text-slate-500">{{ __(':count active students', ['count' => number_format($group->active_enrollments_count)]) }}</p>
                        </a>
                    @empty
                        <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">{{ __('No planned or active groups yet.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Recent Customers') }}</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ __('Latest records') }}</h3>
                    </div>
                    <a href="{{ route('customers.index') }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900">{{ __('View all') }}</a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($recentCustomers as $customer)
                        @php $name = trim($customer->first_name . ' ' . $customer->last_name); @endphp
                        <a href="{{ route('customers.show', $customer) }}" class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-4 transition hover:border-slate-300 hover:bg-slate-50">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $name ?: __('Unnamed customer') }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $customer->category?->parent ? $customer->category->parent->name . ' / ' : '' }}{{ $customer->category?->name ?: __('No category') }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $customer->status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20' }}">{{ __(\Illuminate\Support\Str::headline($customer->status)) }}</span>
                        </a>
                    @empty
                        <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">{{ __('No customers yet.') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Payroll Queue') }}</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ __('Draft payrolls this month') }}</h3>
                    </div>
                    <a href="{{ route('employees.index') }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900">{{ __('View employees') }}</a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse($draftPayrolls as $payroll)
                        <a href="{{ route('employees.show', $payroll->employee) }}" class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200 px-4 py-4 transition hover:border-slate-300 hover:bg-slate-50">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $payroll->employee?->display_name ?: __('Unnamed employee') }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ __('Payroll period: :month/:year', ['month' => $payroll->month, 'year' => $payroll->year]) }}</p>
                            </div>
                            <span class="text-sm font-semibold text-slate-900">{{ $money($payroll->net_salary) }}</span>
                        </a>
                    @empty
                        <div class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">{{ __('No draft payrolls for this month.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
