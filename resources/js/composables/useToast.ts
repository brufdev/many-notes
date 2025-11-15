import { ref } from 'vue';

type ToastType = 'success' | 'error' | 'warning' | 'info';

interface Toast {
    id: number;
    message: string;
    type: ToastType;
    duration?: number;
}

const toasts = ref<Toast[]>([]);
let toastId = 0;

export function useToast() {
    const createToast = (message: string, type: ToastType, duration: number = 2500) => {
        const id = ++toastId;

        toasts.value.push({
            id,
            message,
            type,
            duration,
        });

        if (duration > 0) {
            setTimeout(() => {
                destroyToast(id);
            }, duration);
        }
    };

    const destroyToast = (id: number) => {
        toasts.value = toasts.value.filter(toast => toast.id !== id);
    };

    return { toasts, createToast, destroyToast };
}
