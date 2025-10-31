<script setup lang="ts">
import { onMounted } from 'vue';

onMounted(() => {
    document.querySelector('#app')?.classList.add('flex', 'flex-col', 'h-dvh');
});
</script>

<template>
    <main
        class="bg-light-base-200 dark:bg-base-950 text-light-base-950 dark:text-base-50 flex flex-grow flex-col place-content-center"
    >
        <div class="mx-auto my-4 sm:my-8">
            <img
                class="max-h-14 sm:max-h-16 md:max-h-20"
                src="/public/assets/logo.png"
                alt="Many Notes"
            />
        </div>

        <div class="md:container md:mx-auto">
            <div
                class="bg-light-base-50 dark:bg-base-900 flex flex-col gap-4 rounded-lg p-4 sm:mx-auto sm:w-full sm:max-w-sm sm:p-6"
            >
                <slot />
            </div>
        </div>
    </main>
</template>
