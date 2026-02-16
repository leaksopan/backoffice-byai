<!-- Modulify Module Layout: shared shell for all /m/{moduleKey} pages -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? ($activeModule?->name ? $activeModule->name.' | '.setting('app.name', config('app.name')) : setting('app.name', config('app.name'))) }}</title>
        @if (setting('branding.favicon'))
            <link rel="icon" href="{{ Storage::disk('public')->url(setting('branding.favicon')) }}">
        @endif
        @include('partials.theme-init')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen app-bg text-slate-900 transition-colors dark:text-slate-100">
        <div x-data="moduleShell()" x-init="init()" x-cloak class="min-h-screen" @keydown.escape.window="if (!isDesktop) mobileOpen = false">
            <div
                x-show="mobileOpen && !isDesktop"
                x-cloak
                class="fixed inset-0 z-30 bg-black/40"
                @click="mobileOpen = false"
            ></div>

            <aside
                id="app-sidebar"
                class="fixed inset-y-0 left-0 z-40 flex flex-col overflow-visible border-r glass-surface glass-divider transform"
                :class="[
                    isDesktop
                        ? (desktopCollapsed ? 'w-20 translate-x-0' : 'w-72 translate-x-0')
                        : (mobileOpen ? 'w-72 translate-x-0' : 'w-72 -translate-x-full'),
                    'transition-all duration-300'
                ]"
            >
                <div class="border-b glass-divider px-3 py-3">
                    <div class="flex items-center justify-between gap-2">
                        <a
                            href="{{ route('modules.dashboard') }}"
                            class="group relative flex min-w-0 items-center gap-3 rounded-lg px-2 py-2 hover:bg-white/5 focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400"
                            :aria-label="appName ?? 'Modulify'"
                        >
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg glass-chip text-slate-700 dark:text-slate-200">
                                <x-brand-logo class="h-5 w-5" />
                            </span>

                            <span
                                class="whitespace-nowrap text-base font-semibold text-slate-800 dark:text-slate-100"
                                x-cloak
                                x-show="!desktopCollapsed"
                                x-transition.opacity
                            >
                                <span x-text="appName ?? 'Modulify'"></span>
                            </span>

                            <span class="sr-only" x-text="appName ?? 'Modulify'"></span>

                            <div
                                x-cloak
                                x-show="desktopCollapsed && isDesktop"
                                class="glass-tooltip pointer-events-none invisible absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 px-3 py-2 text-sm font-medium opacity-0 translate-x-1 transition group-hover:visible group-hover:opacity-100 group-hover:translate-x-0 group-focus-within:visible group-focus-within:opacity-100 group-focus-within:translate-x-0 group-focus-visible:visible group-focus-visible:opacity-100 group-focus-visible:translate-x-0"
                            >
                                <span x-text="appName ?? 'Modulify'"></span>
                            </div>
                        </a>

                        <button
                            type="button"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100/80 dark:text-slate-300 dark:hover:bg-slate-800/70 md:hidden"
                            @click="mobileOpen = false"
                            aria-label="Close sidebar"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-3 py-3">
                    <a
                        class="group relative flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-700 transition hover:glass-soft dark:text-slate-200"
                        :class="desktopCollapsed ? 'justify-center px-2' : ''"
                        href="{{ route('modules.dashboard') }}"
                        :aria-label="desktopCollapsed ? 'Modules Dashboard' : null"
                    >
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19 3 12l7-7m-7 7h18" />
                        </svg>

                        <span x-cloak x-show="!desktopCollapsed" x-transition.opacity>Modules</span>

                        <div
                            x-cloak
                            x-show="desktopCollapsed && isDesktop"
                            class="glass-tooltip pointer-events-none invisible absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 px-3 py-2 text-sm font-medium opacity-0 translate-x-1 transition group-hover:visible group-hover:opacity-100 group-hover:translate-x-0 group-focus-within:visible group-focus-within:opacity-100 group-focus-within:translate-x-0 group-focus-visible:visible group-focus-visible:opacity-100 group-focus-visible:translate-x-0"
                        >
                            Modules Dashboard
                        </div>
                    </a>
                </div>

                <nav class="flex-1 space-y-5 px-3 pb-5">
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
                                x-show="!desktopCollapsed"
                            >
                                {{ $normalizedGroup }}
                            </div>

                            <div class="mt-2 space-y-1">
                                @foreach ($menus as $menu)
                                    @php
                                        $menuHref = '#';
                                        $menuActiveTargets = [];

                                        if ($menu->route_name && Route::has($menu->route_name)) {
                                            $menuHref = route($menu->route_name);
                                            $menuActiveTargets[] = $menu->route_name;
                                            $menuActiveTargets[] = $menu->route_name.'.*';

                                            $menuRoutePath = trim((string) parse_url($menuHref, PHP_URL_PATH), '/');

                                            if ($menuRoutePath !== '') {
                                                $menuActiveTargets[] = $menuRoutePath.'*';
                                            }
                                        } elseif ($menu->route_name) {
                                            $menuActiveTargets[] = $menu->route_name;
                                            $menuActiveTargets[] = $menu->route_name.'.*';
                                        } elseif ($menu->url) {
                                            $menuHref = $menu->url;
                                        }

                                        if ($menu->url) {
                                            $menuUrlPath = (string) parse_url($menu->url, PHP_URL_PATH);
                                            $menuUrlPath = trim($menuUrlPath !== '' ? $menuUrlPath : $menu->url, '/');

                                            if ($menuUrlPath !== '') {
                                                $menuActiveTargets[] = $menuUrlPath.'*';
                                            }
                                        }

                                        $menuActiveTargets = array_values(array_unique(array_filter($menuActiveTargets)));
                                        $menuIsActive = nav_is_active($menuActiveTargets);
                                    @endphp

                                    <div class="group relative">
                                        <a
                                            class="flex items-center gap-3 rounded-xl border px-3 py-2.5 text-sm transition {{ nav_active_class($menuActiveTargets, 'border-sky-400/60 bg-sky-100/55 text-sky-700 shadow-sm dark:border-sky-500/55 dark:bg-sky-900/35 dark:text-sky-200', 'border-transparent text-slate-700 dark:text-slate-200 hover:glass-soft') }}"
                                            :class="desktopCollapsed ? 'justify-center px-2' : ''"
                                            href="{{ $menuHref }}"
                                            :aria-label="desktopCollapsed ? @js($menu->label) : null"
                                            aria-current="{{ $menuIsActive ? 'page' : 'false' }}"
                                        >
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg glass-chip {{ $menuIsActive ? 'text-sky-700 dark:text-sky-200' : 'text-slate-600 dark:text-slate-300' }}">
                                                <span class="h-4 w-4">
                                                    <x-menu-icon :name="$menu->icon" />
                                                </span>
                                            </span>

                                            <span class="truncate font-medium" x-cloak x-show="!desktopCollapsed" x-transition.opacity>
                                                {{ $menu->label }}
                                            </span>
                                        </a>

                                        <div
                                            x-cloak
                                            x-show="desktopCollapsed && isDesktop"
                                            class="glass-tooltip pointer-events-none invisible absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 px-3 py-2 text-sm font-medium opacity-0 translate-x-1 transition group-hover:visible group-hover:opacity-100 group-hover:translate-x-0 group-focus-within:visible group-focus-within:opacity-100 group-focus-within:translate-x-0 group-focus-visible:visible group-focus-visible:opacity-100 group-focus-visible:translate-x-0"
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

            <div
                class="min-h-screen"
                :class="[
                    isDesktop ? (desktopCollapsed ? 'pl-20' : 'pl-72') : 'pl-0',
                    'transition-[padding-left] duration-300'
                ]"
            >
                <header class="sticky top-0 z-20 border-b glass-surface glass-divider">
                    <div class="flex w-full items-center justify-between gap-2 px-4 py-3 sm:px-6 lg:px-8">
                        <div class="flex min-w-0 items-center gap-2">
                            <button
                                type="button"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl glass-soft text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100"
                                @click="isDesktop ? (desktopCollapsed = !desktopCollapsed) : (mobileOpen = true)"
                                aria-label="Toggle sidebar"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            <div class="truncate text-sm font-semibold text-slate-700 dark:text-slate-100">
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
                                <svg class="h-5 w-5" x-cloak x-show="themeMode === 'light'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="4" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2m10-10h-2M4 12H2m16.2 7.8-1.4-1.4M7.2 7.2 5.8 5.8m12.4 0-1.4 1.4M7.2 16.8l-1.4 1.4" />
                                </svg>
                                <svg class="h-5 w-5" x-cloak x-show="themeMode === 'dark'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z" />
                                </svg>
                            </button>

                            @auth
                                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button
                                        class="btn-ghost px-3 py-2"
                                        type="button"
                                        @click="open = !open"
                                    >
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full glass-chip text-xs font-semibold uppercase">
                                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                        </span>
                                        <span class="hidden max-w-[9rem] truncate sm:inline">{{ auth()->user()->name }}</span>
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.2 7.2a.75.75 0 0 1 1.06 0L10 10.94l3.74-3.74a.75.75 0 1 1 1.06 1.06l-4.27 4.27a.75.75 0 0 1-1.06 0L5.2 8.26a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <div
                                        class="absolute right-0 mt-2 w-48 overflow-hidden rounded-xl border glass-surface glass-divider"
                                        x-cloak
                                        x-show="open"
                                        x-transition.origin.top.right
                                    >
                                        <a class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-100/80 dark:text-slate-100 dark:hover:bg-slate-800/70" href="{{ route('profile.edit') }}">
                                            Profile
                                        </a>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button class="block w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100/80 dark:text-slate-100 dark:hover:bg-slate-800/70" type="submit">
                                                Logout
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endauth
                        </div>
                    </div>
                </header>

                <main class="px-4 py-6 sm:px-6 lg:px-8">
                    @yield('content')
                </main>
            </div>
        </div>

        <script>
            (() => {
                const appName = @js(setting('app.name', config('app.name')));

                const createModuleShell = () => ({
                    mobileOpen: false,
                    desktopCollapsed: (() => {
                        const saved = localStorage.getItem('modulify.sidebarCollapsed') ?? localStorage.getItem('modulify-sidebar-collapsed');

                        return saved === '1' || saved === 'true';
                    })(),
                    isDesktop: window.matchMedia('(min-width: 768px)').matches,
                    themeMode: 'light',
                    appName,

                    init() {
                        const mq = window.matchMedia('(min-width: 768px)');

                        const apply = () => {
                            this.isDesktop = mq.matches;

                            if (this.isDesktop) {
                                this.mobileOpen = false;
                            }
                        };

                        apply();

                        if (typeof mq.addEventListener === 'function') {
                            mq.addEventListener('change', apply);
                        } else if (typeof mq.addListener === 'function') {
                            mq.addListener(apply);
                        }

                        window.addEventListener('resize', apply);

                        this.$watch('desktopCollapsed', (value) => {
                            localStorage.setItem('modulify.sidebarCollapsed', value ? '1' : '0');
                        });

                        const syncTheme = () => {
                            const manager = window.modulifyTheme;
                            const preference = manager ? manager.getPreference() : 'system';

                            this.themeMode = manager ? manager.resolveTheme(preference) : 'light';
                        };

                        syncTheme();
                        window.addEventListener('modulify-theme-changed', syncTheme);
                    },

                    toggleTheme() {
                        const manager = window.modulifyTheme;

                        if (! manager) {
                            return;
                        }

                        this.themeMode = manager.toggleTheme();
                    },
                });

                window.moduleShell = createModuleShell;

                document.addEventListener('alpine:init', () => {
                    Alpine.data('moduleShell', createModuleShell);
                });
            })();
        </script>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
