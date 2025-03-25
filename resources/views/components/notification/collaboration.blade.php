@props(['vaultId'])

<x-menu.item @click="$wire.dispatchTo('modals.notification-invite', 'open-modal', { vault: {{ $vaultId }} })">
    {{ __('You have been invited to join a vault') }}
</x-menu.item>
