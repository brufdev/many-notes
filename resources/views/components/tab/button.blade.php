@props(['tab'])

<button
    {{ $attributes }}
    class="h-min"
    type="button"
    role="tab"
    aria-controls="tabpanel{{ $tab }}"
    :aria-selected="selectedTab === '{{ $tab }}'"
    :tabindex="selectedTab === '{{ $tab }}' ? '0' : '-1'"
    :class="selectedTab === '{{ $tab }}' ? 'border-b-2' : 'hover:border-b-2'"
    @click="selectedTab = '{{ $tab }}'"
>
    {{ $slot }}
</button>
