<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ setting('app.name', config('app.name')) }}</title>
        @if (setting('branding.favicon'))
            <link rel="icon" href="{{ Storage::disk('public')->url(setting('branding.favicon')) }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @include('partials.theme-init')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="min-h-screen app-bg font-sans antialiased text-slate-900 dark:text-slate-100"
        x-data="{ ...modulifyThemeState() }"
        x-init="initTheme()"
    >
        <div class="relative flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
            <div class="absolute right-4 top-4 sm:right-6 sm:top-6">
                <button
                    class="glass-soft inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-700 transition hover:text-slate-900 dark:text-slate-200 dark:hover:text-slate-50"
                    type="button"
                    @click="toggleTheme"
                    aria-label="Toggle theme"
                >
                    <svg class="h-5 w-5" x-cloak x-show="themeMode === 'light'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="4" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2m10-10h-2M4 12H2m16.2 7.8-1.4-1.4M7.2 7.2 5.8 5.8m12.4 0-1.4 1.4M7.2 16.8l-1.4 1.4" />
                    </svg>
                    <svg class="h-5 w-5" x-cloak x-show="themeMode === 'dark'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" />
                    </svg>
                </button>
            </div>

            <div class="w-full max-w-md space-y-6">
                <div class="flex justify-center">
                    <a class="inline-flex items-center justify-center rounded-2xl glass-chip p-4" href="/">
                        <x-brand-logo class="h-16 w-16" />
                    </a>
                </div>

                <div class="glass-card w-full px-6 py-6 sm:px-8 sm:py-7">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
