<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? ('Modules Dashboard | '.setting('app.name', config('app.name'))) }}</title>
        @if (setting('branding.favicon'))
            <link rel="icon" href="{{ Storage::disk('public')->url(setting('branding.favicon')) }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            (() => {
                const storedTheme = localStorage.getItem('modulify-theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (storedTheme === 'dark' || (! storedTheme && prefersDark)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>
    </head>
    <body
        class="min-h-screen bg-app-surface text-slate-900 dark:text-slate-100"
        x-data="{
            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
            toggleTheme() {
                this.theme = this.theme === 'dark' ? 'light' : 'dark';
                document.documentElement.classList.toggle('dark', this.theme === 'dark');
                localStorage.setItem('modulify-theme', this.theme);
            }
        }"
    >
        <div class="min-h-screen">
            <header class="border-b border-slate-200/80 glass-panel dark:border-slate-700/80">
                <div class="flex w-full items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                    <div class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-600 dark:text-slate-300">
                        {{ setting('app.name', config('app.name')) }}
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl glass-soft text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100"
                            type="button"
                            @click="toggleTheme"
                            aria-label="Toggle theme"
                        >
                            <svg class="h-5 w-5" x-cloak x-show="theme === 'light'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="4" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2m10-10h-2M4 12H2m16.2 7.8-1.4-1.4M7.2 7.2 5.8 5.8m12.4 0-1.4 1.4M7.2 16.8l-1.4 1.4" />
                            </svg>
                            <svg class="h-5 w-5" x-cloak x-show="theme === 'dark'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" />
                            </svg>
                        </button>

                        @auth
                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                <button
                                    class="inline-flex items-center gap-2 rounded-xl glass-soft px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-100"
                                    type="button"
                                    @click="open = !open"
                                >
                                    <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.2 7.2a.75.75 0 0 1 1.06 0L10 10.94l3.74-3.74a.75.75 0 1 1 1.06 1.06l-4.27 4.27a.75.75 0 0 1-1.06 0L5.2 8.26a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div
                                    class="absolute right-0 mt-2 w-44 overflow-hidden rounded-xl border border-slate-200/80 glass-panel dark:border-slate-700"
                                    x-cloak
                                    x-show="open"
                                    x-transition.origin.top.right
                                >
                                    <a class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-100/90 dark:text-slate-100 dark:hover:bg-slate-800/70" href="{{ route('profile.edit') }}">
                                        Profile
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="block w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100/90 dark:text-slate-100 dark:hover:bg-slate-800/70" type="submit">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            </header>

            <main class="w-full px-4 py-8 sm:px-6 lg:px-8">
                @yield('content')
            </main>
        </div>
    </body>
</html>
