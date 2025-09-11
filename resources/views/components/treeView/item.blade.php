@props(['node'])

<li
    {{ $attributes }}
    class="my-0.5 items-center justify-between"
    x-data="{ accordionOpen: false }"
>
    {{ $slot }}
</li>
