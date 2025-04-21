<div
    {{ $attributes }}
    tabindex="-1"
    x-data="{ modalOpen: false }"
    x-modelable="modalOpen"
    @close-modal.window="modalOpen = false"
>
    {{ $slot }}
</div>
