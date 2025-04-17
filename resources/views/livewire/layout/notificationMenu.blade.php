<div
    x-init="
        Echo.private('User.{{ auth()->user()->id }}')
            .listen('UserNotifiedEvent', (e) => {
                $wire.$refresh();
            });
    "
>
    <x-menu anchorElement="$refs.header" anchorOffset="-15" wide>
        <x-menu.button>
            <x-icons.bell class="w-5 h-5" />

            @if (count($notifications))
                <span class="animate-ping absolute top-1 right-0.5 block h-1 w-1 rounded-full ring-2 ring-error-400 bg-error-600"></span>
            @endif
        </x-menu.button>

        <x-menu.items>
            <x-menu.close>
                @forelse ($notifications as $notification)
                    <div wire:key="notification-{{ $notification['id'] }}">
                        <x-dynamic-component component="notification.{{ lcfirst($notification['type']) }}" :$notification />
                    </div>
                @empty
                    <div class="px-3 text-sm">
                        {{ __('No notifications') }}
                    </div>
                @endforelse
            </x-menu.close>
        </x-menu.items>
    </x-menu>
</div>
