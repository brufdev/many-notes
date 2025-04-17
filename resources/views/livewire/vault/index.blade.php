<div class="flex flex-col h-dvh" x-data="vaults">
    <x-layouts.appHeader>
        <div class="flex items-center gap-4"></div>

        <div class="flex items-center gap-4">
            <livewire:layout.notification-menu />
            <livewire:layout.user-menu />
        </div>
    </x-layouts.appHeader>

    <x-layouts.appMain>
        <div class="relative flex w-full">
            <div class="absolute inset-0 overflow-y-auto">
                <div class="flex flex-col h-full">
                    <div
                        class="sticky top-0 z-[5] flex items-center justify-between p-4 bg-light-base-50 dark:bg-base-900">
                        <h2 class="text-lg">{{ __('My vaults') }}</h2>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="$wire.dispatchTo('modals.import-vault', 'open-modal')"
                                title="{{ __('Import vault') }}">
                                <x-icons.arrowUpTray class="w-5 h-5" />
                            </button>

                            <x-modal wire:model="showCreateModal">
                                <x-modal.open>
                                    <button type="button" title="{{ __('Create vault') }}">
                                        <x-icons.plus class="w-5 h-5" />
                                    </button>
                                </x-modal.open>

                                <x-modal.panel title="{{ __('Create new vault') }}">
                                    <x-form wire:submit="create" class="flex flex-col gap-6">
                                        <x-form.input name="form.name" label="{{ __('Name') }}" type="text"
                                            required autofocus />

                                        <div class="flex justify-end">
                                            <x-form.submit label="{{ __('Create') }}" target="create" />
                                        </div>
                                    </x-form>
                                </x-modal.panel>
                            </x-modal>
                        </div>
                    </div>
                    <div class="flex flex-col flex-grow px-4">
                        <div class="flex-grow h-0 min-h-full">
                            <ul class="flex flex-col" wire:loading.class="opacity-50">
                                @forelse ($this->vaults as $vault)
                                    <livewire:vault.row
                                        :vaultId="$vault->id"
                                        :key="'vault-row-' . $vault->id"
                                        @vault-export="export({{ $vault->id }})"
                                        @vault-delete="delete({{ $vault->id }})"
                                    />
                                @empty
                                    <li class="items-center pt-3 pb-4">
                                        <p>{{ __('You have no vaults yet.') }}</p>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <livewire:modals.import-vault />
    </x-layouts.appMain>
</div>

@script
    <script>
        Alpine.data('vaults', () => ({
            init() {
                if ($wire.toastErrorMessage.length > 0) {
                    this.$nextTick(() => {
                        this.$dispatch('toast', { message: $wire.toastErrorMessage, type: 'error' });
                    });
                }

                Echo.private('User.{{ auth()->user()->id }}')
                    .listen('CollaborationDeletedEvent', (e) => {
                        $wire.$refresh();
                    })
                    .listen('VaultListUpdatedEvent', (e) => {
                        $wire.$refresh();
                    });
            }
        }));
    </script>
@endscript
