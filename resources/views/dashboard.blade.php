<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-900">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2" />
                        <circle cx="9" cy="7" r="4" stroke-width="1.8" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                    </svg>
                </div>
                <p class="mt-5 text-sm font-medium text-slate-500">Total Customers</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ \App\Models\Customer::count() }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="mt-5 text-sm font-medium text-slate-500">Active Customers</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ \App\Models\Customer::where('status', 'active')->count() }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5h18M3 12h18M3 19h10" />
                    </svg>
                </div>
                <p class="mt-5 text-sm font-medium text-slate-500">Sources Tracked</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ \App\Models\Customer::query()->whereNotNull('source')->distinct('source')->count('source') }}</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="mt-5 text-sm font-medium text-slate-500">Recent Leads</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ \App\Models\Customer::query()->whereDate('created_at', today())->count() }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.4fr_0.9fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">Overview</p>
                        <h3 class="mt-2 text-2xl font-semibold text-slate-900">Your workspace is ready</h3>
                    </div>
                    <a href="{{ route('customers.index') }}" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Open Customers
                    </a>
                </div>

                <div class="mt-6 rounded-3xl bg-slate-50 p-6">
                    <p class="text-sm leading-7 text-slate-600">
                        Use the sidebar to move between your main sections, and use the customers module to search, review, and manage your records in one place.
                    </p>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">Quick Actions</p>
                <div class="mt-5 space-y-3">
                    <a href="{{ route('customers.create') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-4 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                        <span>Add Customer</span>
                        <span class="text-slate-400">New</span>
                    </a>
                    <a href="{{ route('customers.index') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-4 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                        <span>Review Customers</span>
                        <span class="text-slate-400">List</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center justify-between rounded-2xl border border-slate-200 px-4 py-4 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                        <span>Update Profile</span>
                        <span class="text-slate-400">Edit</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
