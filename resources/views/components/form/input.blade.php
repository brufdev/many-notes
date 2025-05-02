<label class="flex flex-col gap-2 text-base font-medium">
    @if ($attributes->has('label'))
        <span>
            {{ $label }}
            @if ($attributes->has('required'))
                <span class="opacity-75 text-error-500" aria-hidden="true">*</span>
            @endif
        </span>
    @endif

    <input
        type="{{ $attributes->merge(['type' => 'text'])->get('type') }}"
        wire:model="{{ $name }}"
        @class([
            'block w-full px-2 py-1.5 bg-light-base-100 dark:bg-base-800 text-light-base-700 dark:text-base-200 rounded-lg focus:ring-0 focus:outline focus:outline-0 border border-light-base-300 dark:border-base-500 focus:border-light-base-600 dark:focus:border-base-400',
            'border border-error-500 focus:border-error-700 dark:border-error-500 dark:focus:border-error-700' => $errors->has($name),
        ])
        @error($name)
            aria-invalid="true"
            aria-description="{{ $message }}"
        @enderror
        @if ($attributes->has('autofocus'))
            autofocus
        @endif
        @if ($attributes->has('placeholder'))
            placeholder="{{ $placeholder }}"
        @endif
    />

    @error($name)
        <p class="text-sm text-error-500" aria-live="assertive">{{ $message }}</p>
    @enderror
</label>
