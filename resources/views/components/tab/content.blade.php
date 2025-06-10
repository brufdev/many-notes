@props(['tab'])

<div
    {{ $attributes }}
    id="tabpanel{{ $tab }}"
    role="tabpanel"
    aria-label="{{ $tab }}"
    x-show="selectedTab === '{{ $tab }}'"
    x-cloak
>
    {{ $slot }}
</div>
