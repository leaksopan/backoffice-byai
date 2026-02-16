@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-lg border border-emerald-300/80 bg-emerald-50/90 px-3 py-2 text-sm font-medium text-emerald-700 dark:border-emerald-600/70 dark:bg-emerald-950/40 dark:text-emerald-300']) }}>
        {{ $status }}
    </div>
@endif
