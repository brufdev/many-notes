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
        <div class="w-full" x-data="{ selectedTab: $wire.entangle('selectedTab') }">
            <div
                class="flex gap-2 overflow-x-auto"
                role="tablist"
                aria-label="tab options"
                @keydown.right.prevent="$focus.wrap().next()"
                @keydown.left.prevent="$focus.wrap().previous()"
            >
                <button
                    class="h-min"
                    type="button"
                    role="tab"
                    aria-controls="tabpanelList"
                    @click="selectedTab = 'users'"
                    x-bind:aria-selected="selectedTab === 'users'"
                    x-bind:tabindex="selectedTab === 'users' ? '0' : '-1'"
                    x-bind:class="selectedTab === 'users' ? 'border-b-2' : 'hover:border-b-2'"
                >
                    {{ __('Users') }}
                </button>
                <button
                    class="h-min"
                    type="button"
                    role="tab"
                    aria-controls="tabpanelInviteUser"
                    x-bind:aria-selected="selectedTab === 'inviteUser'"
                    x-bind:tabindex="selectedTab === 'inviteUser' ? '0' : '-1'"
                    x-bind:class="selectedTab === 'inviteUser' ? 'border-b-2' : 'hover:border-b-2'"
                    @click="selectedTab = 'inviteUser'"
                >
                    {{ __('Invite user') }}
                </button>
            </div>
            <div class="py-4">
                <div id="tabpanelList" role="tabpanel" aria-label="list" x-show="selectedTab === 'users'" x-cloak>
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
                </div>
                <div id="tabpanelInviteUser" role="tabpanel" aria-label="inviteUser" x-show="selectedTab === 'inviteUser'" x-cloak>
                    <x-form class="flex flex-col gap-6" wire:submit.prevent="invite">
                        <x-form.input name="form.email" label="{{ __('Email') }}" type="email" required />

                        <div class="flex justify-end">
                            <x-form.submit label="{{ __('Invite') }}" />
                        </div>
                    </x-form>
                </div>
            </div>
        </div>
    </x-modal.panel>
</x-modal>
