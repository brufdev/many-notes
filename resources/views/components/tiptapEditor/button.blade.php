@props([
    'icon' => false,
    'toggle' => false,
])

<button
    {{ $attributes }}
    @class([
        'flex items-center w-full gap-2 px-2 py-1 text-sm text-left transition-colors border rounded text-light-base-950 dark:text-base-50 border-light-base-400 dark:border-base-700 hover:enabled:text-light-base-50 disabled:opacity-50',
        'hover:enabled:bg-primary-400 dark:hover:enabled:bg-primary-500' => $toggle,
        'hover:enabled:bg-light-base-400 dark:hover:enabled:bg-base-700' => !$toggle,
    ])
    type="button"
>
    @if ($icon)
        <x-dynamic-component component="icons.{{ $icon }}" class="w-4.5 h-4.5" />
    @endif
    <span class="sr-only">{{ $attributes->get('title') }}</span>
</button>
