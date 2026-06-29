<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold leading-tight tracking-tight text-slate-900">
            {{ __('Programs') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('System Settings') }}</p>
                    <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ __('Programs') }}</h1>
                    <p class="mt-2 text-sm text-slate-500">{{ __('Manage learning paths, course tracks, and program catalogs.') }}</p>
                </div>
                @can('create programs')
                    <a href="{{ route('programs.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        {{ __('New Program') }}
                    </a>
                @endcan
            </div>

            @if(session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    @endpush
</x-app-layout>
