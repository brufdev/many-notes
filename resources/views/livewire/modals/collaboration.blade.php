<x-modal
    x-init="
        Echo.private('User.{{ auth()->user()->id }}')
            .listen('CollaborationAcceptedEvent', (e) => {
                $wire.$refresh();
            })
            .listen('CollaborationDeclinedEvent', (e) => {
                $wire.$refresh();
            });
    "
    wire:model="show"
>
    <x-modal.panel title="{{ __('Collaboration') }}" top>
        <x-tab
            default="users"
            @collaboration-select-tab.window="selectedTab = $event.detail.tab"
        >
            <x-slot:tabs>
                <x-tab.button tab="users">
                    {{ __('Users') }}
                </x-tab.button>
                <x-tab.button tab="inviteUser">
                    {{ __('Invite user') }}
                </x-tab.button>
            </x-slot:tabs>
            <x-tab.content tab="users">
                <table class="w-full table-auto">
                    <tbody>
                        <tr class="w-full pt-3 pb-4 border-b last:border-b-0 border-light-base-300 dark:border-base-500">
                            <td>
                                <div class="flex items-center">
                                    <p class="mb-1" title="{{ $vault->user->email }}">{{ $vault->user->name }}</p>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center">
                                    <p class="mb-1">{{ __('Owner') }}</p>
                                </div>
                            </td>
                            <td class="text-right">
                                <button>
                                    <x-icons.trash class="w-4 h-4 opacity-50" />
                                </button>
                            </td>
                        </tr>
                        @foreach ($collaborators as $collaborator)
                            <tr
                                class="w-full pt-3 pb-4 border-b last:border-b-0 border-light-base-300 dark:border-base-500"
                                wire:key="collaborator-{{ $collaborator->id }}"
                            >
                                <td>
                                    <div class="flex items-center">
                                        <p class="mb-1" title="{{ $collaborator->email }}">{{ $collaborator->name }}</p>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center">
                                        <p class="mb-1">{{ $collaborator->pivot->accepted ? __('Accepted') : __('Pending') }}</p>
                                    </div>
                                </td>
                                <td class="text-right">
                                    <button
                                        title="{{ __('Delete') }}"
                                        wire:click="delete({{ $collaborator->id }})"
                                        wire:confirm="{{ __('Are you sure you want to delete this collaborator?') }}"
                                    >
                                        <x-icons.trash class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-tab.content>
            <x-tab.content tab="inviteUser">
                <x-form class="flex flex-col gap-6" wire:submit.prevent="invite">
                    <x-form.input name="form.email" label="{{ __('Email') }}" type="email" required />

                    <div class="flex justify-end">
                        <x-form.submit label="{{ __('Invite') }}" />
                    </div>
                </x-form>
            </x-tab.content>
        </x-tab>
    </x-modal.panel>
</x-modal>
