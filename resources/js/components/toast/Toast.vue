<script setup lang="ts">
import { useToast } from '@/composables';
import { CheckCircle, ExclamationCircle, ExclamationTriangle, InformationCircle } from '@/icons';

const { toasts, destroyToast } = useToast();

const getIcon = (type: string) => {
    switch (type) {
        case 'success':
            return { icon: CheckCircle, class: 'text-success-600' };
        case 'error':
            return { icon: ExclamationCircle, class: 'text-error-600' };
        case 'warning':
            return { icon: ExclamationTriangle, class: 'text-warning-600' };
        case 'info':
        default:
            return { icon: InformationCircle, class: 'text-info-600' };
    }
};
</script>

<template>
    <div class="fixed right-0 bottom-4 z-50 z-[99] w-full max-w-xs">
        <TransitionGroup name="toast" tag="div" class="mx-4 flex flex-col gap-2">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="bg-light-base-200 dark:bg-base-950 border-light-base-300 dark:border-base-500 w-full rounded-md border duration-300 ease-out"
            >
                <button class="flex w-full gap-1 p-3 text-sm" @click="destroyToast(toast.id)">
                    <component
                        :is="getIcon(toast.type).icon"
                        :class="['h-5 w-5', getIcon(toast.type).class]"
                    ></component>

                    <span>{{ toast.message }}</span>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateY(-10px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
