<x-modal wire:model="show">
    <x-modal.panel>
        <x-form wire:submit="import">
            <div
                class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed rounded-lg border-light-base-300 dark:border-base-500"
                x-data="{ uploading: false, progress: 0 }"
                @livewire-upload-start="uploading = true"
                @livewire-upload-finish="uploading = false"
                @livewire-upload-cancel="uploading = false"
                @livewire-upload-error="uploading = false"
                @livewire-upload-progress="progress = $event.detail.progress"
            >
                <label
                    for="file-upload"
                    class="flex flex-col items-center justify-center w-full h-full gap-2 text-base font-medium cursor-pointer"
                >
                    <h6 class="font-semibold">{{ __('Browse file to import') }}</h6>
                    <span class="text-sm">{{ __('Image, video, audio, note or pdf file') }}</span>
                    <span class="text-sm">{{ __('Max size ') . ini_get('upload_max_filesize') }}</span>

                    @error('file')
                        <p class="text-sm text-center text-error-500" aria-live="assertive">{{ $message }}</p>
                    @enderror

                    <!-- Progress Bar -->
                    <div x-show="uploading">
                        <progress max="100" x-bind:value="progress" class="w-64 h-1 mt-2"></progress>
                    </div>
                </label>

                <input type="file" id="file-upload" class="hidden" wire:model="file" accept="{{ $fileMimes }}" />
            </div>
        </x-form>
    </x-modal.panel>
</x-modal>
