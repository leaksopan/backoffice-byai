@extends('layouts.module')

@section('content')
    <div class="max-w-4xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">App Branding</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Update global application identity used across all layouts.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form class="glass-card space-y-6 p-6" method="POST" action="{{ route('settings.branding.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">App Name</label>
                    <input class="input-glass mt-2 w-full" name="app_name" type="text" value="{{ old('app_name', $form['app_name']) }}" required>
                    @error('app_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Tagline</label>
                    <input class="input-glass mt-2 w-full" name="tagline" type="text" value="{{ old('tagline', $form['tagline']) }}">
                    @error('tagline') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Logo Light</label>
                    <input class="input-glass w-full text-sm" name="logo_light" type="file" accept=".png,.jpg,.jpeg,.svg,.webp">
                    @if ($form['logo_light'])
                        <div class="glass-card rounded-lg p-3">
                            <img class="h-14 w-auto object-contain" src="{{ Storage::disk('public')->url($form['logo_light']) }}" alt="Logo Light preview">
                        </div>
                    @endif
                    @error('logo_light') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Logo Dark</label>
                    <input class="input-glass w-full text-sm" name="logo_dark" type="file" accept=".png,.jpg,.jpeg,.svg,.webp">
                    @if ($form['logo_dark'])
                        <div class="rounded-lg border border-slate-700 bg-slate-900 p-3">
                            <img class="h-14 w-auto object-contain" src="{{ Storage::disk('public')->url($form['logo_dark']) }}" alt="Logo Dark preview">
                        </div>
                    @endif
                    @error('logo_dark') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Favicon</label>
                    <input class="input-glass w-full text-sm" name="favicon" type="file" accept=".png,.ico,.svg">
                    @if ($form['favicon'])
                        <div class="glass-card rounded-lg p-3">
                            <img class="h-10 w-10 object-contain" src="{{ Storage::disk('public')->url($form['favicon']) }}" alt="Favicon preview">
                        </div>
                    @endif
                    @error('favicon') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <button class="btn-primary" type="submit">
                    Save Branding
                </button>
            </div>
        </form>
    </div>
@endsection
