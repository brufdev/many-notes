<div x-show="menuOpen" x-anchor.bottom-end="$refs.button" @click.away="menuOpen = false" x-cloak
    class="min-w-[10rem] z-[35] border bg-light-base-200 dark:bg-base-950 border-light-base-300 dark:border-base-500 rounded-md shadow-lg p-1.5 text-light-base-950 dark:text-base-50">
    {{ $slot }}
</div>
