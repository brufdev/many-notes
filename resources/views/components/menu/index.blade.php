@props(['wide' => false])

<div {{ $attributes->merge(['class' => 'relative']) }}
    x-data="{ menuOpen: false, wide: {{ $wide ? 'true' : 'false' }} }"
>
    {{ $slot }}
</div>
