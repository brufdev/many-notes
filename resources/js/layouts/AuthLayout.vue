<script setup lang="ts">
import ModalManager from '@/components/modal/ModalManager.vue';
import Toast from '@/components/toast/Toast.vue';
import Spinner from '@/icons/Spinner.vue';
import { useLayoutStore } from '@/stores/layout';
import AppLayout from './AppLayout.vue';

const layoutStore = useLayoutStore();
</script>

<template>
    <AppLayout>
        <header
            class="bg-light-base-200 dark:bg-base-800 text-light-base-950 dark:text-base-50 border-light-base-300 dark:border-base-500 border-b print:hidden"
        >
            <div class="flex justify-between px-4 py-5">
                <slot name="header" />
            </div>
        </header>

        <main class="bg-light-base-50 dark:bg-base-900 relative flex flex-1 overflow-hidden">
            <slot />
        </main>

        <div
            v-if="layoutStore.isAppLoading"
            class="bg-light-base-50 dark:bg-base-900 fixed inset-0 z-40 opacity-50"
        >
            <div class="flex h-full items-center justify-center">
                <Spinner class="h-5 w-5 animate-spin" />
            </div>
        </div>

        <ModalManager />

        <Toast />
    </AppLayout>
</template>
