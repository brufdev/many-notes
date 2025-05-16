@props(['top' => false])

@aware(['title'])

<template x-teleport="body">
    <div
        class="fixed inset-0 z-50 w-full overflow-x-hidden overflow-y-auto max-h-full flex justify-center {{ $top ? 'sm:mt-5' : 'items-end sm:items-center' }}"
        x-show="modalOpen"
        @keydown.escape.window="modalOpen = false"
    >
        <div class="fixed inset-0 opacity-50 bg-base-950"></div>
        <div class="relative w-full max-w-2xl">
            <div
                x-trap="modalOpen"
                @click.outside="modalOpen = false"
            >
                <div class="flex justify-between p-6 pb-0 rounded-t-lg bg-light-base-200 dark:bg-base-950 text-light-base-950 dark:text-base-50">
                    <h3 class="text-lg">{{ $title }}</h3>
                    <x-modal.close class="flex items-center">
                        <button type="button">
                            <span class="sr-only">{{ __('Close') }}</span>
                            <x-icons.xMark class="w-5 h-5" />
                        </button>
                    </x-modal.close>
                </div>
                <div class="p-6 shadow-lg sm:rounded-b-lg bg-light-base-200 dark:bg-base-950">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</template>
