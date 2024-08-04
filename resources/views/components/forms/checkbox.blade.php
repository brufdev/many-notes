<label class="inline-flex items-center text-base font-medium">
    <input
        type="checkbox"
        wire:model="{{ $name }}"
        class="rounded shadow-sm border-light-base-300 dark:border-base-500 bg-light-base-100 dark:bg-base-800 text-primary-600 focus:ring-0 focus:ring-offset-0 focus-visible:outline focus-visible:outline-1 focus-visible:outline-offset-2 focus-visible:outline-light-base-600 dark:focus-visible:outline-base-400"
    >

    <span class="text-sm text-gray-600 ms-2 dark:text-gray-400">{{ $label }}</span>
</label>
