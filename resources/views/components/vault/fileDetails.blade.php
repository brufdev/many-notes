@props(['header' => ''])

<div class="flex flex-col flex-grow">
    <div class="sticky top-0 flex flex-col gap-4 py-4 z-15 bg-light-base-50 dark:bg-base-900">
        <div class="z-[5]">
            <div class="flex justify-between">
                <input
                    class="flex flex-grow p-0 px-1 text-lg bg-transparent border-0 focus:ring-0 focus:outline-0"
                    type="text"
                    spellcheck="false"
                    wire:model.live.debounce.500ms="nodeForm.name"
                />

                <div class="flex items-center gap-2">
                    <span class="flex items-center" wire:loading.flex wire:target="nodeForm.name, nodeForm.content">
                        <x-icons.spinner class="w-4 h-4 animate-spin" />
                    </span>
                    <div x-show="users.length > 1">
                        <x-menu class="flex">
                            <button
                                x-ref="button"
                                @mouseenter="menuOpen = true"
                                @mouseleave="menuOpen = false"
                            >
                                <x-icons.userGroup class="w-[1.1rem] h-[1.1rem]" />
                                <span class="absolute bottom-0 right-0 w-1.5 h-1.5 rounded-full border bg-success-500 border-light-base-200 dark:border-base-950"></span>
                            </button>

                            <x-menu.items>
                                <div class="px-3">
                                    {{ __('Users in this file') }}
                                </div>
                                <x-menu.itemDivider></x-menu.itemDivider>
                                <template x-for="user in users">
                                    <x-menu.item x-text="user.name"></x-menu.item>
                                </template>
                            </x-menu.items>
                        </x-menu>
                    </div>
                    <button title="{{ __('Close file') }}" @click="closeFile">
                        <x-icons.xMark class="w-5 h-5" />
                    </button>
                </div>
            </div>

            @error('nodeForm.name')
                <p class="text-sm text-error-500" aria-live="assertive">{{ $message }}</p>
            @enderror
        </div>
        {{ $header }}
    </div>
    <div class="h-full pb-4">
        {{ $slot }}
    </div>
</div>
