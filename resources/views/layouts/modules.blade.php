<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Modules Dashboard' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-900">
        <div class="min-h-screen">
            <header class="bg-white shadow-sm">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                    <a class="flex items-center" href="{{ route('modules.dashboard') }}">
                        <x-application-logo class="block h-8 w-auto fill-current text-slate-900" />
                    </a>
                    <div class="flex items-center gap-4 text-sm text-slate-600">
                        @auth
                            <span>{{ auth()->user()->name }}</span>
                            <a class="hover:text-slate-900" href="{{ route('profile.edit') }}">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="hover:text-slate-900" type="submit">Logout</button>
                            </form>
                        @endauth
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                @yield('content')
            </main>
        </div>
    </body>
</html>
