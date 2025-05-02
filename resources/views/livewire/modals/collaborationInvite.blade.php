<x-modal wire:model="show">
    <x-modal.panel title="{{ __('New invite') }}">
        <p>
            {{ __(sprintf('%s has invited you to join the vault', $username)) }}
            <span class="font-semibold">{{ $name }}</span>.
        </p>
        <div class="gap-2 mt-6 text-sm sm:flex sm:flex-row-reverse">
            <button
                class="inline-flex justify-center w-full px-2 py-1.5 mt-3 border rounded-md sm:w-auto sm:mt-0 border-primary-300 dark:border-primary-600 bg-primary-400 dark:bg-primary-500 hover:bg-primary-300 dark:hover:bg-primary-600 text-light-base-50"
                wire:click="accept"
            >
                {{ __('Accept') }}
            </button>
            <button
                class="inline-flex justify-center w-full px-2 py-1.5 mt-3 border rounded-md sm:w-auto sm:mt-0 border-light-base-400 dark:border-base-700 bg-light-base-300 dark:bg-base-500 hover:bg-light-base-400 dark:hover:bg-base-700 text-light-base-950 dark:text-base-50"
                wire:click="decline"
                wire:confirm="{{ __('Are you sure you want to decline this invitation?') }}"
            >
                {{ __('Decline') }}
            </button>
        </div>
    </x-modal.panel>
</x-modal>
