<label class="inline-flex justify-between cursor-pointer">
    <span class="me-3 text-sm font-medium">{{ $label }}</span>
    <input type="checkbox" value="" class="sr-only peer" wire:model="{{ $name }}">
    <div class="relative w-11 h-6 bg-light-base-600 dark:bg-base-500 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-light-base-100 after:rounded-full after:w-5 after:h-5 after:transition-all peer-checked:bg-primary-300 dark:peer-checked:bg-primary-600"></div>
</label>
