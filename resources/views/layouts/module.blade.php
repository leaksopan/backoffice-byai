<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? ($activeModule?->name ? $activeModule->name.' | '.setting('app.name', config('app.name')) : setting('app.name', config('app.name'))) }}</title>
        @if (setting('branding.favicon'))
            <link rel="icon" href="{{ Storage::disk('public')->url(setting('branding.favicon')) }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
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
        class="min-h-screen bg-app-surface text-slate-900 transition-colors dark:text-slate-100"
        x-data="{
            mobileSidebarOpen: false,
            sidebarCollapsed: localStorage.getItem('modulify-sidebar-collapsed') === 'true',
            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
            toggleSidebar() {
                this.sidebarCollapsed = ! this.sidebarCollapsed;
                localStorage.setItem('modulify-sidebar-collapsed', this.sidebarCollapsed ? 'true' : 'false');
            },
            toggleTheme() {
                this.theme = this.theme === 'dark' ? 'light' : 'dark';
                document.documentElement.classList.toggle('dark', this.theme === 'dark');
                localStorage.setItem('modulify-theme', this.theme);
            }
        }"
    >
        <div class="min-h-screen lg:flex">
            <div
                class="fixed inset-0 z-30 bg-slate-950/35 lg:hidden"
                x-cloak
                x-show="mobileSidebarOpen"
                x-transition.opacity
                @click="mobileSidebarOpen = false"
            ></div>

            <aside
                class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col border-r border-slate-200/60 bg-white/80 shadow-2xl shadow-slate-900/10 backdrop-blur-xl transition-all duration-300 dark:border-slate-800 dark:bg-slate-950/80 dark:shadow-black/30 lg:translate-x-0"
                :class="[
                    mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full',
                    sidebarCollapsed ? 'lg:w-[72px]' : 'lg:w-72'
                ]"
            >
                <div class="relative flex h-16 items-center justify-center border-b border-slate-200/70 px-3 dark:border-slate-800/80">
                    <button
                        class="absolute left-3 inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 lg:hidden"
                        type="button"
                        @click="mobileSidebarOpen = false"
                        aria-label="Close sidebar"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <a class="flex items-center gap-2" href="{{ route('modules.dashboard') }}">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl glass-chip text-slate-700 dark:text-slate-200">
                            <x-brand-logo class="h-6 w-6" />
                        </span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200" x-cloak x-show="!sidebarCollapsed" x-transition.opacity>
                            {{ setting('app.name', config('app.name')) }}
                        </span>
                    </a>
                </div>

                <div class="px-3 py-3">
                    <a
                        class="flex items-center gap-2 rounded-xl glass-soft py-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-600 transition hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100"
                        :class="sidebarCollapsed ? 'justify-center px-2' : 'px-3'"
                        href="{{ route('modules.dashboard') }}"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19 3 12l7-7m-7 7h18" />
                        </svg>
                        <span x-cloak x-show="!sidebarCollapsed">Modules</span>
                    </a>
                </div>

                <nav class="flex-1 space-y-5 overflow-y-auto px-3 pb-5">
                    @forelse ($menuGroups as $group => $menus)
                        @php
                            $normalizedGroup = strtoupper((string) $group);
                        @endphp

                        @if ($normalizedGroup === 'ADMIN' && ! $showAdminGroup)
                            @continue
                        @endif

                        <div>
                            <div
                                class="px-3 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500/90 dark:text-slate-400/90"
                                x-cloak
                                x-show="!sidebarCollapsed"
                            >
                                {{ $normalizedGroup }}
                            </div>

                            <div class="mt-2 space-y-1">
                                @foreach ($menus as $menu)
                                    @php
                                        $menuHref = '#';

                                        if ($menu->route_name && Route::has($menu->route_name)) {
                                            $menuHref = route($menu->route_name);
                                        } elseif ($menu->url) {
                                            $menuHref = $menu->url;
                                        }
                                    @endphp

                                    <div class="relative" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false">
                                        <a
                                            class="flex items-center gap-3 rounded-xl text-sm text-slate-600 transition hover:glass-soft hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/70 dark:hover:text-slate-100"
                                            :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'px-3 py-2.5'"
                                            href="{{ $menuHref }}"
                                        >
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg glass-chip text-slate-600 dark:text-slate-300">
                                                <span class="h-4 w-4">
                                                    <x-menu-icon :name="$menu->icon" />
                                                </span>
                                            </span>

                                            <span class="truncate font-medium" x-cloak x-show="!sidebarCollapsed" x-transition.opacity>
                                                {{ $menu->label }}
                                            </span>
                                        </a>

                                        <div
                                            class="pointer-events-none absolute left-full top-1/2 z-50 ml-2 -translate-y-1/2 rounded-lg border border-slate-200/80 bg-white/95 px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-xl shadow-slate-900/10 backdrop-blur dark:border-slate-700 dark:bg-slate-900/95 dark:text-slate-100"
                                            x-cloak
                                            x-show="sidebarCollapsed && hover"
                                            x-transition.opacity
                                        >
                                            {{ $menu->label }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="px-3 py-4 text-sm text-slate-500 dark:text-slate-400">No menus available.</div>
                    @endforelse
                </nav>
            </aside>

            <div class="flex min-h-screen flex-1 flex-col lg:min-w-0">
                <header class="sticky top-0 z-20 border-b border-slate-200/80 glass-panel dark:border-slate-700/80">
                    <div class="flex w-full items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-2">
                            <button
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl glass-soft text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100 lg:hidden"
                                type="button"
                                @click="mobileSidebarOpen = true"
                                aria-label="Open sidebar"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            <button
                                class="hidden h-10 w-10 items-center justify-center rounded-xl glass-soft text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100 lg:inline-flex"
                                type="button"
                                @click="toggleSidebar"
                                aria-label="Toggle sidebar width"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h4v14H4zm6 0h10v14H10z" />
                                </svg>
                            </button>

                            <div class="text-sm font-semibold text-slate-700 dark:text-slate-100">
                                {{ $activeModule?->name ?? 'Module' }}
                            </div>
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

                <main class="w-full flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    @yield('content')
                </main>
            </div>
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
