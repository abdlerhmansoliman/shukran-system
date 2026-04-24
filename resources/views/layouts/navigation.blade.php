@php
    $sidebar = config('sidebar');
@endphp

<div
    x-show="sidebarOpen && !isDesktop"
    x-transition.opacity
    @click="closeSidebar()"
    class="fixed inset-0 z-30 bg-slate-950/40"
    style="display: none;"
></div>

<aside
    class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col border-r border-slate-200 bg-white shadow-xl transition-transform duration-300"
    :style="sidebarTransform()"
>
    <div class="flex h-20 items-center border-b border-slate-200 px-6">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-900 text-white shadow-sm">
                <x-application-logo class="h-6 w-6 fill-current" />
            </span>
            <span>
                <span class="block text-sm font-semibold uppercase tracking-[0.24em] text-slate-400">{{ data_get($sidebar, 'brand.eyebrow', 'Shukran') }}</span>
                <span class="block text-lg font-semibold text-slate-900">{{ __(data_get($sidebar, 'brand.title', 'Admin Dashboard')) }}</span>
            </span>
        </a>

        <button
            x-show="!isDesktop"
            x-cloak
            type="button"
            @click="closeSidebar()"
            class="ml-auto inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 text-slate-500 transition hover:bg-slate-50"
            aria-label="{{ __('Close sidebar') }}"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto px-4 py-6">
        @foreach(data_get($sidebar, 'sections', []) as $section)
            <div class="{{ $loop->first ? 'space-y-1' : 'mt-8 space-y-1' }}">
                <p class="px-3 text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __($section['title']) }}</p>

                @foreach($section['items'] as $item)
                    @php
                        $isActive = collect($item['active'] ?? [])->contains(fn ($pattern) => request()->routeIs($pattern));
                        $isDisabled = $item['disabled'] ?? false;
                        $href = (!$isDisabled && !empty($item['route'])) ? route($item['route']) : null;
                    @endphp

                    @if($href)
                        <a href="{{ $href }}" class="sidebar-link {{ $isActive ? 'sidebar-link-active' : '' }}">
                    @else
                        <div class="sidebar-link {{ $isDisabled ? 'sidebar-link-muted' : '' }} {{ $isActive ? 'sidebar-link-active' : '' }}">
                    @endif
                        <span class="sidebar-link-icon">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @foreach($item['icon']['paths'] ?? [] as $path)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $path }}" />
                                @endforeach
                                @foreach($item['icon']['circles'] ?? [] as $circle)
                                    <circle cx="{{ $circle['cx'] }}" cy="{{ $circle['cy'] }}" r="{{ $circle['r'] }}" stroke-width="1.8" />
                                @endforeach
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block truncate">{{ __($item['title']) }}</span>
                            @if(!empty($item['subtitle']))
                                <span class="mt-0.5 block truncate text-xs font-normal text-slate-400">{{ __($item['subtitle']) }}</span>
                            @endif
                        </span>
                    @if($href)
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="border-t border-slate-200 p-4">
        <div class="rounded-3xl bg-slate-900 px-4 py-5 text-white shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Signed In') }}</p>
            <p class="mt-3 text-base font-semibold">{{ Auth::user()->name }}</p>
            <p class="mt-1 text-sm text-slate-300">{{ Auth::user()->email }}</p>
        </div>
    </div>
</aside>
