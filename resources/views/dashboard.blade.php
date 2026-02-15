<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-100">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto w-full max-w-7xl">
            <div class="glass-card overflow-hidden">
                <div class="p-6 text-slate-800 dark:text-slate-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
