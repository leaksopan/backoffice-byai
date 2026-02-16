<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 rounded-xl border border-rose-400/70 bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition duration-150 hover:bg-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2 focus:ring-offset-transparent dark:border-rose-500/70 dark:bg-rose-500 dark:text-white dark:hover:bg-rose-400']) }}>
    {{ $slot }}
</button>
