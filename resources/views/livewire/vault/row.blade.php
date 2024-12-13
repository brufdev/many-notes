<li class="items-center pt-3 pb-4 border-b border-light-base-300 dark:border-base-500">
    <div class="flex items-center justify-between w-full">
        <div class="flex items-center">
            <div class="flex flex-col">
                <h6 class="mb-1 font-semibold">
                    <a href="/vaults/{{ $vault->id }}" wire:navigate>{{ $vault->name }}</a>
                </h6>
                <span class="text-xs">
                    {{ __('Updated on') }}
                    {{ $vault->updated_at->format('F j, Y') }}
                </span>
            </div>
        </div>
        <div class="flex flex-col items-center justify-center">
            <x-menu>
                <x-menu.button>
                    <x-icons.ellipsisVertical class="w-5 h-5" />
                </x-menu.button>

                <x-menu.items>
                    <x-menu.close>
                        <x-modal>
                            <x-modal.open>
                                <x-menu.item>
                                    <x-icons.pencilSquare class="w-4 h-4" />
                                    {{ __('Edit') }}
                                </x-menu.item>
                            </x-modal.open>

                            <x-modal.panel title="{{ __('Edit vault') }}">
                                <x-form wire:submit="update" class="flex flex-col gap-6">
                                    <x-form.input name="form.name" label="{{ __('Name') }}" type="text" required
                                        autofocus />

                                    <div class="flex justify-end">
                                        <x-form.submit label="{{ __('Edit') }}" target="edit" />
                                    </div>
                                </x-form>
                            </x-modal.panel>
                        </x-modal>

                        <x-menu.item x-on:click="$dispatch('vault-export')">
                            <x-icons.arrowDownTray class="w-4 h-4" />
                            {{ __('Export') }}
                        </x-menu.item>

                        <x-menu.item wire:confirm="{{ __('Are you sure you want to delete this vault?') }}"
                            wire:click="$dispatch('vault-delete')">
                            <x-icons.trash class="w-4 h-4" />
                            {{ __('Delete') }}
                        </x-menu.item>
                    </x-menu.close>
                </x-menu.items>
            </x-menu>
        </div>
    </div>
</li>
