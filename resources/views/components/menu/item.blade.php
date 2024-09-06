<div>
    <button
        class="flex items-center w-full gap-2 px-3 py-1 text-sm text-left transition-colors rounded hover:bg-light-base-400 dark:hover:bg-base-700 text-light-base-950 dark:text-base-50"
        {{ $attributes->merge([
            'type' => 'button',
        ]) }}
    >
        {{ $slot }}
    </button>
</div>
