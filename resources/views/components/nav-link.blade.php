@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-sky-500 px-1 pt-1 text-sm font-medium leading-5 text-sky-700 transition duration-150 ease-in-out focus:border-sky-600 focus:outline-none dark:text-sky-300'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-slate-600 transition duration-150 ease-in-out hover:border-slate-300 hover:text-slate-900 focus:border-slate-300 focus:outline-none focus:text-slate-900 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:text-slate-100';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
