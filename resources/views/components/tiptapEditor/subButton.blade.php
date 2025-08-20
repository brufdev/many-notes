@props([
    'icon' => false,
    'iconRotate' => false,
])

<span click="isToolbarOpen = false">
    <button
        {{ $attributes }}
        class="flex items-center gap-2 px-2 py-1 text-sm text-left transition-colors rounded hover:bg-light-base-400 dark:hover:bg-base-700 text-light-base-950 dark:text-base-50"
        type="button"
    >
        @if ($icon)
            <x-dynamic-component component="icons.{{ $icon }}" class="w-4.5 h-4.5{{ $iconRotate ? ' rotate-180' : '' }}" />
        @endif
        <span class="sr-only">{{ $attributes->get('title') }}</span>
    </button>
</span>
