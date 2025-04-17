<x-modal wire:model="show">
    <x-modal.panel title="{{ __('Choose a template') }}">
        @if ($templates && count($templates))
            <ul class="flex flex-col gap-2" wire:loading.class="opacity-50">
                @foreach ($templates as $template)
                    <li wire:key="mde-template-{{ $template->id }}">
                        <button type="button"
                            class="flex w-full gap-2 py-1 hover:text-light-base-950 dark:hover:text-base-50"
                            wire:click="insertTemplate({{ $template->id }}); modalOpen = false"
                        >
                            <span class="overflow-hidden whitespace-nowrap text-ellipsis"
                                title="{{ $template->name }}"
                            >
                                {{ $template->name }}
                            </span>
                        </button>
                    </li>
                @endforeach
            </ul>
        @else
            <p>{{ __('No templates found') }}</p>
        @endif
    </x-modal.panel>
</x-modal>
