<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? ($activeModule?->name ?? 'Module') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900" x-data="{ sidebarOpen: false }">
        <div class="min-h-screen lg:flex">
            <div
                class="fixed inset-0 z-20 bg-slate-900/40 lg:hidden"
                x-show="sidebarOpen"
                x-transition.opacity
                @click="sidebarOpen = false"
            ></div>

            <aside
                class="fixed inset-y-0 left-0 z-30 w-64 transform bg-white transition lg:static lg:translate-x-0 lg:border-r lg:border-slate-200"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            >
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-5">
                    <a class="inline-flex items-center gap-3" href="{{ route('modules.dashboard') }}">
                        <x-application-logo class="block h-8 w-auto fill-current text-slate-900" />
                        <span class="text-base font-semibold text-slate-900">Modulify</span>
                    </a>
                    <button
                        class="inline-flex items-center justify-center rounded-md border border-slate-200 p-1.5 text-slate-600 hover:text-slate-900 lg:hidden"
                        type="button"
                        @click="sidebarOpen = false"
                        aria-label="Close sidebar"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-5 py-4">
                    <a class="inline-flex w-full items-center justify-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100" href="{{ route('modules.dashboard') }}">
                        Back to Modules Dashboard
                    </a>
                </div>
                <nav class="space-y-6 px-5 pb-8">
                    @forelse ($menuGroups as $group => $menus)
                        @if (strtoupper($group) === 'ADMIN' && ! $showAdminGroup)
                            @continue
                        @endif
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                {{ $group }}
                            </div>
                            <div class="mt-3 space-y-1">
                                @foreach ($menus as $menu)
                                    <a class="block rounded-md px-3 py-2 text-sm text-slate-700 hover:bg-slate-100 hover:text-slate-900" href="{{ route($menu->route_name) }}">
                                        {{ $menu->label }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">No menus available.</div>
                    @endforelse
                </nav>
            </aside>

            <div class="flex-1">
                <header class="border-b border-slate-200 bg-white">
                    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                        <button
                            class="inline-flex items-center justify-center rounded-md border border-slate-200 p-2.5 text-slate-600 hover:text-slate-900 lg:hidden"
                            type="button"
                            @click="sidebarOpen = true"
                            aria-label="Open sidebar"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="flex items-center gap-4 text-sm text-slate-600">
                            @auth
                                <span>{{ auth()->user()->name }}</span>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="hover:text-slate-900" type="submit">Logout</button>
                                </form>
                            @endauth
                        </div>
                    </div>
                </header>

                <main class="mx-auto w-full max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                    @yield('content')
                </main>
            </div>
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
