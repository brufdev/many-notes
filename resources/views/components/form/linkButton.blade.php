@props([
    'primary' => false,
    'full' => false,
])

<a
    {{ $attributes }}
    @class([
        'flex items-center gap-2 px-2 py-1.5 border rounded-md',
        'border-primary-300 dark:border-primary-600 bg-primary-400 dark:bg-primary-500 hover:bg-primary-300 dark:hover:bg-primary-600 text-light-base-50' => $primary,
        'border-light-base-400 dark:border-base-700 bg-light-base-300 dark:bg-base-500 hover:bg-light-base-400 dark:hover:bg-base-700 text-light-base-950 dark:text-base-50' => !$primary,
        'w-full justify-center' => $full,
    ])
>
    {{ $slot }}
</a>
