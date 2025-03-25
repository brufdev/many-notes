<div>
    <x-menu>
        <x-menu.button>
            <x-icons.bell class="w-5 h-5" />
        </x-menu.button>

        <x-menu.items>
            @forelse ($notifications as $notification)
                <x-notification.collaboration vaultId="{{ $notification->data['vault_id'] }}" />
            @empty
                <div class="px-3 text-sm">
                    {{ __('No notifications') }}
                </div>
            @endforelse
        </x-menu.items>
    </x-menu>
</div>
