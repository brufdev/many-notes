<div
    x-show="menuOpen"
    x-anchor.bottom-end="$refs.button"
    @click.away="menuOpen = false"
    class="min-w-[10rem] z-20 border bg-light-base-200 dark:bg-base-950 border-light-base-300 dark:border-base-500 divide-y rounded-md shadow-lg p-1.5 outline-nonea"
    x-cloak
>
    {{ $slot }}
</div>