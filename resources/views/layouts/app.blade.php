@php
    $currentLocale = app()->getLocale();
    $currentLocaleConfig = config('locales.supported.' . $currentLocale, config('locales.supported.en'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $currentLocale) }}" dir="{{ data_get($currentLocaleConfig, 'dir', 'ltr') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <script>
            (() => {
                const storedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = storedTheme ?? (prefersDark ? 'dark' : 'light');

                document.documentElement.classList.toggle('dark', theme === 'dark');
                document.documentElement.dataset.theme = theme;
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-slate-100 text-slate-900 transition-colors duration-300">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen">
            @include('layouts.navigation')

            <div class="lg:pl-72">
                <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur">
                    <div class="flex h-20 items-center justify-between px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-4">
                            <button
                                type="button"
                                @click="sidebarOpen = true"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-slate-200 text-slate-600 transition hover:bg-slate-50 lg:hidden"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">{{ __('Admin Panel') }}</p>
                                <div class="mt-1 text-lg font-semibold text-slate-900">
                                    @isset($header)
                                        {{ $header }}
                                    @elseif(View::hasSection('header'))
                                        @yield('header')
                                    @else
                                        {{ __('Dashboard') }}
                                    @endisset
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex items-center rounded-2xl border border-slate-200 bg-white p-1 shadow-sm">
                                @foreach(config('locales.supported', []) as $locale => $localeConfig)
                                    <a
                                        href="{{ route('locale.switch', $locale) }}"
                                        class="rounded-xl px-3 py-2 text-xs font-semibold transition {{ $currentLocale === $locale ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}"
                                    >
                                        {{ $localeConfig['label'] }}
                                    </a>
                                @endforeach
                            </div>

                            <button
                                type="button"
                                id="theme-toggle"
                                class="inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                                aria-label="Toggle theme"
                                title="Toggle theme"
                            >
                                <svg id="theme-icon-light" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v2.25M12 18.75V21M4.97 4.97l1.59 1.59M17.44 17.44l1.59 1.59M3 12h2.25M18.75 12H21M4.97 19.03l1.59-1.59M17.44 6.56l1.59-1.59" />
                                    <circle cx="12" cy="12" r="4" stroke-width="1.8" />
                                </svg>
                                <svg id="theme-icon-dark" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 12.79A9 9 0 1111.21 3c0 .3-.01.6-.04.9A7 7 0 0020.1 12.83c.3-.03.6-.04.9-.04z" />
                                </svg>
                            </button>

                            <div class="hidden rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-500 sm:block">
                                {{ now()->format('d M Y') }}
                            </div>

                            <x-dropdown align="right" width="56">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-slate-50">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">
                                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(Auth::user()->name ?? 'A', 0, 2)) }}
                                        </span>
                                        <span class="hidden text-left sm:block">
                                            <span class="block font-semibold text-slate-900">{{ Auth::user()->name }}</span>
                                            <span class="block text-xs text-slate-500">{{ Auth::user()->email }}</span>
                                        </span>
                                        <svg class="h-4 w-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Profile') }}
                                    </x-dropdown-link>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault();
                                                            this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </header>

                <main class="min-h-[calc(100vh-5rem)] p-4 sm:p-6 lg:p-8">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </main>
            </div>
        </div>

        @stack('scripts')
        <script>
            (() => {
                const toggleButton = document.getElementById('theme-toggle');
                const lightIcon = document.getElementById('theme-icon-light');
                const darkIcon = document.getElementById('theme-icon-dark');

                const syncThemeUI = () => {
                    const theme = document.documentElement.dataset.theme || 'light';
                    const isDark = theme === 'dark';

                    if (lightIcon) {
                        lightIcon.style.display = isDark ? 'none' : 'block';
                    }

                    if (darkIcon) {
                        darkIcon.style.display = isDark ? 'block' : 'none';
                    }

                    if (toggleButton) {
                        toggleButton.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
                        toggleButton.setAttribute('title', isDark ? 'Light mode' : 'Dark mode');
                    }
                };

                syncThemeUI();

                toggleButton?.addEventListener('click', () => {
                    const currentTheme = document.documentElement.dataset.theme || 'light';
                    const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';

                    document.documentElement.classList.toggle('dark', nextTheme === 'dark');
                    document.documentElement.dataset.theme = nextTheme;
                    localStorage.setItem('theme', nextTheme);
                    syncThemeUI();
                });
            })();
        </script>
    </body>
</html>
