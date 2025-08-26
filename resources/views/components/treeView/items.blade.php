@props(['root'])

<ul
    @if ($root)
        class="relative w-full"
    @else
        class="relative w-full pl-2 ml-2 border-l-2 border-light-base-400 dark:border-base-500"
        x-show="accordionOpen"
        x-collapse
        x-cloak
    @endunless
>
    {{ $slot }}
</ul>
