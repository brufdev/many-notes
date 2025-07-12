<x-modal wire:model="show">
    <x-modal.panel title="{{ __('Settings') }}">
        <x-form wire:submit.prevent="edit" class="flex flex-col gap-6">
            <x-form.checkboxToggle
                name="form.registration"
                label="{{ __('Registration') }}"
            />
            <x-form.checkboxToggle
                name="form.auto_update_check"
                label="{{ __('Automatic update check') }}"
            />

            <div class="flex justify-end">
                <x-form.submit label="{{ __('Save') }}" />
            </div>
        </x-form>
    </x-modal.panel>
</x-modal>
