@props(['default'])

<div
    {{ $attributes }}
    class="w-full"
    x-data="{ selectedTab: '{{ $default }}' }"
>
    <div
        class="flex gap-2 overflow-x-auto"
        role="tablist"
        aria-label="tab options"
        @keydown.right.prevent="$focus.wrap().next()"
        @keydown.left.prevent="$focus.wrap().previous()"
    >
        {{ $tabs }}
    </div>

    <div class="py-4">
        {{ $slot }}
    </div>
</div>
