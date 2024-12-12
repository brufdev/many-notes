@props(['top' => false])

@aware(['title'])

<template x-teleport="body">
    <div x-show="modalOpen" @keydown.escape.window="modalOpen = false"
        class="fixed inset-0 z-50 pt-0 overflow-y-auto text-light-base-700 dark:text-base-200">
        <div class="fixed inset-0 opacity-50 bg-base-950"></div>

        <div class="relative flex justify-center min-h-full p-0 sm:p-4{{ $top ? '' : ' items-end sm:items-center' }}">
            <div @click.outside="modalOpen = false" x-trap="modalOpen"
                class="relative w-full overflow-hidden shadow-lg sm:mx-auto sm:max-w-md bg-light-base-50 dark:bg-base-900 rounded-t-xl sm:rounded-b-xl">
                <div class="flex justify-between p-6 pb-0">
                    <h3 class="text-lg">{{ $title }}</h3>
                    <x-modal.close class="flex items-center">
                        <button type="button">
                            <span class="sr-only">Close</span>
                            <x-icons.xMark class="w-5 h-5" />
                        </button>
                    </x-modal.close>
                </div>

                <div class="p-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</template>
