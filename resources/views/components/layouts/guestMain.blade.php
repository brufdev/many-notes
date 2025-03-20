<main class="flex flex-col flex-grow place-content-center bg-light-base-200 dark:bg-base-950 text-light-base-950 dark:text-base-50">
    <div class="mx-auto my-4 sm:my-8">
        <img src="{{ asset('assets/logo.png') }}" alt="Many Notes" class="max-h-14 sm:max-h-16 md:max-h-20" />
    </div>

    <div class="md:container md:mx-auto">
        <div class="flex flex-col gap-4 p-4 rounded-lg sm:mx-auto sm:w-full sm:max-w-sm sm:p-6 bg-light-base-50 dark:bg-base-900">
            {{ $slot }}
        </div>
    </div>
</main>
