@props(['node'])

<li
    {{ $attributes }}
    class="my-0.5 first:my-0 items-center justify-between"
    x-data="{ accordionOpen: false }"
>
    {{ $slot }}
</li>
