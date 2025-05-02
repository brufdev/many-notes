<x-modal wire:model="show">
    <x-modal.panel title="Search" top>
        <input
            class="block w-full p-2 border rounded-lg bg-light-base-100 dark:bg-base-800 text-light-base-700 dark:text-base-200 focus:ring-0 focus:outline focus:outline-0 border-light-base-300 dark:border-base-500 focus:border-light-base-600 dark:focus:border-base-400"
            type="text"
            wire:model.live.debounce.500ms="search"
            placeholder="{{ __('Search') }}"
            autofocus
        />

        <div class="mt-4">
            @if (count($nodes))
                <ul class="flex flex-col gap-2" wire:loading.class="opacity-50">
                    @foreach ($nodes as $node)
                        <li wire:key="mde-search-{{ $node['id'] }}">
                            <button
                                class="flex flex-col w-full gap-2 py-1 text-left hover:text-light-base-950 dark:hover:text-base-50"
                                type="button"
                                @click="$dispatch('{{ $searchType == 'image' ? 'mde-image' : 'mde-link' }}', { name: '{{ $node['name'] }}', path: '/{{ $node['full_path_encoded'] . '.' . $node['extension'] }}' }); modalOpen = false"
                            >
                                <span class="flex gap-2">
                                    <span
                                        class="overflow-hidden font-semibold whitespace-nowrap text-ellipsis"
                                        title="{{ $node['name'] }}"
                                    >
                                        {{ $node['name'] }}
                                    </span>

                                    @if ($node['extension'] !== 'md')
                                        <x-treeView.badge>{{ $node['extension'] }}</x-treeView.badge>
                                    @endif
                                </span>
                                @if ($node['dir_name'] !== '')
                                    <span
                                        class="overflow-hidden text-xs whitespace-nowrap text-ellipsis"
                                        title="{{ $node['full_path'] }}"
                                    >
                                        {{ $node['dir_name'] }}
                                    </span>
                                @endif
                            </button>
                        </li>
                    @endforeach
                </ul>
            @else
                <p>{{ __('No results found') }}</p>
            @endif
        </div>
    </x-modal.panel>
</x-modal>
