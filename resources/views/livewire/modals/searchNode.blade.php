<x-modal wire:model="show">
    <x-modal.panel title="Search" top>
        <input type="text" wire:model.live.debounce.500ms="search" placeholder="{{ __('Search') }}" autofocus
            class="block w-full p-2 border rounded-lg bg-light-base-100 dark:bg-base-800 text-light-base-700 dark:text-base-200 focus:ring-0 focus:outline focus:outline-0 border-light-base-300 dark:border-base-500 focus:border-light-base-600 dark:focus:border-base-400" />

        <div class="mt-4">
            @if (count($nodes))
                <ul wire:loading.class="opacity-50">
                    @foreach ($nodes as $node)
                        <li>
                            <button type="button" wire:click="$parent.openFile({{ $node->id }}); modalOpen = false"
                                class="flex w-full gap-2 py-1 text-left">
                                <span title="{{ $node->name }}"
                                    class="overflow-hidden whitespace-nowrap text-ellipsis">
                                    {{ $node->full_path }}
                                </span>

                                @if ($node->extension !== 'md')
                                    <x-treeView.badge>{{ $node->extension }}</x-treeView.badge>
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