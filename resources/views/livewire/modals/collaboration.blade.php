<x-modal wire:model="show">
    <x-modal.panel title="{{ __('Collaboration') }}" top>
        <div class="w-full" x-data="{ selectedTab: 'users' }">
            <div class="flex gap-2 overflow-x-auto" role="tablist" aria-label="tab options"
                x-on:keydown.right.prevent="$focus.wrap().next()" x-on:keydown.left.prevent="$focus.wrap().previous()"
            >
                <button class="p-2 text-smxxx h-min" type="button" role="tab" aria-controls="tabpanelList"
                    x-on:click="selectedTab = 'users'" x-bind:aria-selected="selectedTab === 'users'"
                    x-bind:tabindex="selectedTab === 'users' ? '0' : '-1'"
                    x-bind:class="selectedTab === 'users' ? 'font-boldxxx text-primary border-b-2 border-primary dark:border-primary-dark dark:text-primary-dark' : 'text-on-surface font-medium dark:text-on-surface-dark dark:hover:border-b-outline-dark-strong dark:hover:text-on-surface-dark-strong hover:border-b-2 hover:border-b-outline-strong hover:text-on-surface-strong'"
                >{{ __('Users') }}</button>
                <button class="p-2 text-smxxx h-min" type="button" role="tab" aria-controls="tabpanelLikes"
                    x-on:click="selectedTab = 'likes'" x-bind:aria-selected="selectedTab === 'likes'"
                    x-bind:tabindex="selectedTab === 'likes' ? '0' : '-1'"
                    x-bind:class="selectedTab === 'likes' ? 'font-boldxxx text-primary border-b-2 border-primary dark:border-primary-dark dark:text-primary-dark' : 'text-on-surface font-medium dark:text-on-surface-dark dark:hover:border-b-outline-dark-strong dark:hover:text-on-surface-dark-strong hover:border-b-2 hover:border-b-outline-strong hover:text-on-surface-strong'"
                >{{ __('Invite user') }}</button>
            </div>
            <div class="px-2 py-4 text-on-surface dark:text-on-surface-dark">
                <div x-cloak x-show="selectedTab === 'users'" id="tabpanelList" role="tabpanel" aria-label="list">
                    <table class="w-full table-auto">
                        <tbody>
                            <tr class="w-full pt-3 pb-4 border-b last:border-b-0 border-light-base-300 dark:border-base-500">
                                <td class="">
                                    <div class="flex items-center">
                                        <p class="mb-1" title="{{ $vault->user->email }}">{{ $vault->user->name }}</p>
                                    </div>
                                </td>
                                <td class="">
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
                                <tr class="w-full pt-3 pb-4 border-b last:border-b-0 border-light-base-300 dark:border-base-500">
                                    <td class="">
                                        <div class="flex items-center">
                                            <p class="mb-1" title="{{ $collaborator->email }}">{{ $collaborator->name }}</p>
                                        </div>
                                    </td>
                                    <td class="">
                                        <div class="flex items-center">
                                            <p class="mb-1">{{ $collaborator->pivot->accepted ? __('Accepted') : __('Pending') }}</p>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <button title="{{ __('Delete') }}"
                                            wire:click="delete({{ $collaborator->id }})"
                                            wire:confirm="Are you sure you want to delete this invite?"
                                        >
                                            <x-icons.trash class="w-4 h-4" />
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div x-cloak x-show="selectedTab === 'likes'" id="tabpanelLikes" role="tabpanel" aria-label="likes">
                    <x-form wire:submit.prevent="invite" class="flex flex-col gap-6">
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
