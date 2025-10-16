<main class="relative flex flex-1 overflow-hidden bg-light-base-50 dark:bg-base-900">
    {{ $slot }}

    <x-toast />
    <livewire:modals.collaboration-invite />
</main>
