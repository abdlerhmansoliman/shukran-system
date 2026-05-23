@extends('layouts.app')

@section('content')
<div class="bg-slate-100/70 py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Discount Templates') }}</p>
                <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ __('Discounts') }}</h1>
                <p class="mt-2 text-sm text-slate-500">{{ __('Manage discount templates that can be applied to customer packages.') }}</p>
            </div>

            @can('create discounts')
                <a
                    href="{{ route('discounts.create') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                >
                    {{ __('Add Discount') }}
                </a>
            @endcan
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Type') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Amount') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($discounts as $discount)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">{{ $discount->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">{{ \Illuminate\Support\Str::headline($discount->type) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-500">{{ $discount->amount }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $discount->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                                    {{ \Illuminate\Support\Str::headline($discount->status) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                @can('edit discounts')
                                    <a href="{{ route('discounts.edit', $discount) }}" class="text-slate-600 hover:text-slate-900">{{ __('Edit') }}</a>
                                @endcan
                                @can('delete discounts')
                                    <form action="{{ route('discounts.destroy', $discount) }}" method="POST" class="inline-block ml-3">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 hover:text-rose-900" onclick="return confirm('{{ __('Are you sure?') }}')">{{ __('Delete') }}</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">{{ __('No discounts found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $discounts->links() }}
        </div>
    </div>
</div>
@endsection
