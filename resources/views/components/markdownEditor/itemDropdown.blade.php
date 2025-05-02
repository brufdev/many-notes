<li
    x-data="{ isToolbarOpen: false }"
    x-ref="button"
    @mouseenter="isToolbarOpen = true"
    @mouseleave="isToolbarOpen = false"
>
    {{ $slot }}
</li>
