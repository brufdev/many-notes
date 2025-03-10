@props(['node'])

<li class="items-center justify-between my-0.5" x-data="{ accordionOpen: false }" {{ $attributes }}>
    {{ $slot }}
</li>
