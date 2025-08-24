@props(['root'])

<ul
    @unless ($root)
        class="relative w-full pl-2 ml-2 tree-view-directory"
        x-show="accordionOpen"
        x-collapse
        x-cloak
    @endunless
>
    {{ $slot }}
</ul>
