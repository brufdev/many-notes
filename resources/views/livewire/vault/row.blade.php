<li
    class="items-center pt-3 pb-4 border-b last:border-b-0 border-light-base-300 dark:border-base-500"
    x-init="
        Echo.private('Vault.{{ $this->vault->id }}')
            .listen('VaultFileSystemUpdatedEvent', (e) => {
                $wire.$refresh();
            });
    "
>
    <div class="flex items-center justify-between w-full">
        <a
            class="flex flex-col flex-grow gap-2 w-fullXXX text-startXXX hover:text-primary-600 dark:hover:text-primary-300"
            href="/vaults/{{ $this->vault->id }}"
            title="{{ $this->vault->name }}"
        >
            <span class="flex-grow overflow-hidden whitespace-nowrap text-ellipsis">
                {{ $this->vault->name }}
            </span>
            <span class="overflow-hidden text-xs whitespace-nowrap text-ellipsis text-light-base-700 dark:text-base-200">
                {{ __('Updated on ') . $this->vault->updated_at->format('F j, Y') }}
            </span>
        </a>
        <div class="flex items-center justify-center gap-2">
            @if ($this->vault->collaborators()->wherePivot('accepted', true)->count())
                <span title="{{ __('This vault has collaborators') }}">
                    <x-icons.userGroup class="w-[1.1rem] h-[1.1rem]" />
                </span>
            @endif

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

                        <x-menu.item @click="$dispatch('vault-export')">
                            <x-icons.arrowDownTray class="w-4 h-4" />
                            {{ __('Export') }}
                        </x-menu.item>

                        @if ($this->vault->created_by === auth()->user()->id)
                            <x-menu.item
                                wire:confirm="{{ __('Are you sure you want to delete this vault?') }}"
                                wire:click="$dispatch('vault-delete')"
                            >
                                <x-icons.trash class="w-4 h-4" />
                                {{ __('Delete') }}
                            </x-menu.item>
                        @endif
                    </x-menu.close>
                </x-menu.items>
            </x-menu>
        </div>
    </div>
</li>
