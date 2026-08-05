<script setup lang="ts">
import GuestMenu from '@/components/menu/GuestMenu.vue';
import ModalManager from '@/components/modal/ModalManager.vue';
import AppLayout from './AppLayout.vue';
</script>

<template>
    <AppLayout>
        <main
            class="bg-light-base-50 dark:bg-base-900 text-light-base-950 dark:text-base-50 relative flex-1 overflow-y-auto"
        >
            <div class="absolute top-4 right-4 print:hidden">
                <GuestMenu />
            </div>

            <div class="mx-auto max-w-[48rem] px-4 py-8">
                <slot />
            </div>
        </main>

        <ModalManager />
    </AppLayout>
</template>
