<div class="z-[35] border bg-light-base-200 dark:bg-base-950 border-light-base-300 dark:border-base-500 rounded-md shadow-lg p-1.5 text-light-base-950 dark:text-base-50"
    :class="wide ? 'min-w-[15rem]' : 'min-w-[10rem]'"
    x-show="menuOpen" x-anchor.bottom-end="$refs.button" x-cloak
    @click.away="menuOpen = false"
>
    {{ $slot }}
</div>
