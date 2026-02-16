@props([
    'name' => null,
])

@php
    $icon = strtolower((string) $name);
@endphp

@switch($icon)
    @case('heroicon-o-home')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z" />
        </svg>
        @break
    @case('heroicon-o-users')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m17-4a4 4 0 1 0-3-7.9M9 7a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
        </svg>
        @break
    @case('heroicon-o-shield-check')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3 4 7v6c0 5 3.4 8.8 8 10 4.6-1.2 8-5 8-10V7l-8-4Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4" />
        </svg>
        @break
    @case('heroicon-o-key')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="8" cy="12" r="3" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 12h10m-3 0v2m-3-2v2" />
        </svg>
        @break
    @case('heroicon-o-squares-2x2')
    @case('heroicon-o-table-cells')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <rect x="4" y="4" width="7" height="7" rx="1.2" />
            <rect x="13" y="4" width="7" height="7" rx="1.2" />
            <rect x="4" y="13" width="7" height="7" rx="1.2" />
            <rect x="13" y="13" width="7" height="7" rx="1.2" />
        </svg>
        @break
    @case('heroicon-o-user-plus')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-1.5A4.5 4.5 0 0 0 11.5 15H7.5A4.5 4.5 0 0 0 3 19.5V21" />
            <circle cx="9.5" cy="8" r="3.5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 8v6m-3-3h6" />
        </svg>
        @break
    @case('heroicon-o-rectangle-group')
    @case('heroicon-o-clipboard-document')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <rect x="6" y="3.5" width="12" height="17" rx="2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 8h6M9 12h6M9 16h4" />
        </svg>
        @break
    @case('heroicon-o-cog-6-tooth')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m12 2 1.4 2.4 2.8.4.4 2.8L19 9l-1.4 2.4.4 2.8-2.8.4L13.8 17 12 19l-1.8-2-2.8-.4-.4-2.8L5 9l2.4-1.4.4-2.8 2.8-.4L12 2Z" />
            <circle cx="12" cy="11" r="3.2" />
        </svg>
        @break
    @case('heroicon-o-plus')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14" />
        </svg>
        @break
    @case('heroicon-o-briefcase')
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <rect x="3" y="7" width="18" height="13" rx="2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M3 12h18" />
        </svg>
        @break
    @default
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <rect x="5" y="5" width="14" height="14" rx="3" />
        </svg>
@endswitch
