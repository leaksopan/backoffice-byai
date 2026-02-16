@props([
    'class' => 'h-6 w-6',
])

@php
    $logoLight = setting('branding.logo_light');
    $logoDark = setting('branding.logo_dark');
@endphp

@if ($logoLight || $logoDark)
    @if ($logoLight)
        <img class="{{ $class }} object-contain dark:hidden" src="{{ Storage::disk('public')->url($logoLight) }}" alt="{{ setting('app.name', config('app.name')) }}">
    @endif

    @if ($logoDark)
        <img class="{{ $class }} hidden object-contain dark:block" src="{{ Storage::disk('public')->url($logoDark) }}" alt="{{ setting('app.name', config('app.name')) }}">
    @elseif ($logoLight)
        <img class="{{ $class }} hidden object-contain dark:block" src="{{ Storage::disk('public')->url($logoLight) }}" alt="{{ setting('app.name', config('app.name')) }}">
    @endif
@else
    <x-application-logo class="{{ $class }} fill-current" />
@endif
