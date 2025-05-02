<li @click="isToolbarOpen = false">
    <button
        {{ $attributes }}
        class="flex items-center w-full gap-2 px-3 py-1 text-sm text-left transition-colors rounded hover:bg-light-base-400 dark:hover:bg-base-700 text-light-base-950 dark:text-base-50"
        type="button"
    >
        {{ $slot }}
    </button>
</li>
