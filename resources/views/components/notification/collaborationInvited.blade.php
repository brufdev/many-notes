@props(['notification'])

<x-menu.item @click="$wire.dispatchTo('modals.collaboration-invite', 'open-modal', { vault: {{ $notification['data']['vault_id'] }} })">
    {{ $notification['message'] }}
</x-menu.item>
