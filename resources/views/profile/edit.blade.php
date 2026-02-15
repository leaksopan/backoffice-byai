<x-app-layout>
    <x-slot name="header">
        <div class="pt-1 sm:pt-2">
            <h2 class="text-xl font-semibold leading-tight text-slate-800 dark:text-slate-100">
                {{ __('Profile') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="mx-auto w-full max-w-7xl space-y-6">
            <div>
                <a class="btn-ghost" href="{{ route('modules.dashboard') }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19 3 12l7-7m-7 7h18" />
                    </svg>
                    Back to Modules Dashboard
                </a>
            </div>

            <div class="glass-card p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="glass-card p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="glass-card p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
