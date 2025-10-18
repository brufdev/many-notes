@aware([
    'anchorElement',
    'anchorOffset',
])

<div
    class="z-[35] px-1.5 py-2 border bg-light-base-200 dark:bg-base-950 border-light-base-300 dark:border-base-500 rounded-md shadow-lg text-light-base-950 dark:text-base-50"
    :class="wide ? 'min-w-[15rem]' : 'min-w-[12rem]'"
    x-show="menuOpen"
    x-cloak
    @if ($anchorElement)
        x-anchor.bottom-end{{ $anchorOffset ? '.offset.' . $anchorOffset : '' }}="{{ $anchorElement }}"
    @else
        x-anchor.bottom-end.offset.5="$refs.button"
    @endif
    @click.away="menuOpen = false"
>
    {{ $slot }}
</div>
