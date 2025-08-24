@props(['root'])

<ul
    @if ($root)
        class="relative w-full"
    @else
        class="relative w-full tree-view-directory"
        x-show="accordionOpen"
        x-collapse
        x-cloak
    @endunless
>
    {{ $slot }}
</ul>
