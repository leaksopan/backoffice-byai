<nav
    x-data="{ ...modulifyThemeState(), open: false }"
    x-init="initTheme()"
    class="border-b glass-surface glass-divider"
>
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <a class="inline-flex items-center gap-2 rounded-xl glass-soft px-3 py-2" href="{{ route('modules.dashboard') }}">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg glass-chip">
                        <x-brand-logo class="h-5 w-5" />
                    </span>
                    <span class="hidden text-xs font-semibold uppercase tracking-[0.16em] text-slate-700 dark:text-slate-200 sm:inline">
                        {{ setting('app.name', config('app.name')) }}
                    </span>
                </a>
            </div>

            <div class="hidden items-center gap-2 sm:flex">
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

                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="btn-ghost px-3 py-2">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full glass-chip text-xs font-semibold uppercase">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden max-w-[10rem] truncate text-sm md:inline">{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.2 7.2a.75.75 0 0 1 1.06 0L10 10.94l3.74-3.74a.75.75 0 1 1 1.06 1.06l-4.27 4.27a.75.75 0 0 1-1.06 0L5.2 8.26a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link
                                :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                            >
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-1 flex items-center gap-2 sm:hidden">
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

                <button
                    @click="open = ! open"
                    class="glass-soft inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-600 transition hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100"
                >
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{ 'block': open, 'hidden': ! open }" class="hidden border-t glass-divider sm:hidden">
        <div class="space-y-3 px-4 pb-4 pt-3">
            <div>
                <div class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ Auth::user()->name }}</div>
                <div class="truncate text-xs text-slate-600 dark:text-slate-300">{{ Auth::user()->email }}</div>
            </div>

            <div class="space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link
                        :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                    >
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
