@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg border border-sky-400/60 bg-sky-100/70 px-3 py-2 text-start text-base font-medium text-sky-800 transition duration-150 ease-in-out focus:outline-none dark:border-sky-500/60 dark:bg-sky-900/40 dark:text-sky-100'
            : 'block w-full rounded-lg border border-transparent px-3 py-2 text-start text-base font-medium text-slate-700 transition duration-150 ease-in-out hover:border-slate-300/70 hover:bg-slate-100/80 focus:outline-none dark:text-slate-200 dark:hover:border-slate-600 dark:hover:bg-slate-800/70';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
