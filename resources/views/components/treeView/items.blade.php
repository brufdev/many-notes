@props(['root'])

<ul
    class="relative w-full pl-4 first:pl-0"
    @unless ($root)
        x-show="accordionOpen"
        x-collapse
        x-cloak
    @endunless
>
    {{ $slot }}
</ul>
