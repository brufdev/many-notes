<div
    class="absolute w-full z-[99] max-w-xs right-0 bottom-0 mb-4 px-4"
    x-data="{
        toasts: [],
        remove(key) {
            this.toasts = this.toasts.filter((toast) => toast.key != key);
        },
    }"
    @toast.window="
        const toast = {
            key: Date.now(),
            message: $event.detail.message,
            type: $event.detail.type ?? '',
        };
        toasts.push(toast);
        setTimeout(() => remove(toast.key), $event.detail.duration ?? 2500);
    "
>
    <ul class="flex flex-col gap-2">
        <template x-for="toast in toasts">
            <li class="w-full duration-300 ease-out border rounded-md bg-light-base-200 dark:bg-base-950 border-light-base-300 dark:border-base-500">
                <button class="flex items-start gap-1 p-3 text-sm" @click="remove(toast.key)">
                    <x-icons.checkCircle x-show="toast.type == 'success'" class="w-5 h-5 text-success-600" />
                    <x-icons.exclamationCircle x-show="toast.type == 'error'" class="w-5 h-5 text-error-600" />
                    <x-icons.exclamationTriangle x-show="toast.type == 'warning'" class="w-5 h-5 text-warning-600" />
                    <x-icons.informationCircle x-show="toast.type == 'info'" class="w-5 h-5 text-info-600" />

                    <span x-text="toast.message"></span>
                </button>
            </li>
        </template>
    </ul>
</div>
