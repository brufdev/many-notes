<x-modal wire:model="show">
    <x-modal.panel title="{{ __('New invite') }}">
        <p>{{ __('You have been invited to join a vault') }}</p>
        <button wire:click="accept">{{ __('Accept') }}</button>
        <button wire:click="decline"
            wire:confirm="{{ __('Are you sure you want to decline this invite?') }}"
        >
            {{ __('Decline') }}
        </button>
    </x-modal.panel>
</x-modal>
