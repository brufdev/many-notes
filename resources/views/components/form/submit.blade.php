@props([
    'label',
    'target',
])

<button
    class="relative flex w-full justify-center rounded-md bg-primary-400 dark:bg-primary-500 hover:bg-primary-300 dark:hover:bg-primary-600 text-light-base-50 px-3 py-1.5 text-sm font-semibold leading-6 shadow-sm focus-visible:outline focus-visible:outline-1 focus-visible:outline-offset-2 focus-visible:outline-light-base-600 dark:focus-visible:outline-base-400 disabled:cursor-not-allowed disabled:opacity-75"
    type="submit"
>
    {{ $label }}

    @if (isset($target))
        <span wire:loading.flex wire:target="{{ $target }}" class="absolute top-0 bottom-0 right-0 flex items-center pr-4">
            <x-icons.spinner class="w-5 h-5 animate-spin" />
        </span>
    @endif
</button>
