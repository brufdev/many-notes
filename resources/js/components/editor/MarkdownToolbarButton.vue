<script setup lang="ts">
import { type Component } from 'vue';

withDefaults(
    defineProps<{
        icon: Component;
        toggle?: boolean;
        title?: string;
    }>(),
    {
        toggle: false,
        title: '',
    }
);
</script>

<template>
    <button
        type="button"
        :title="title"
        :class="[
            'text-light-base-950 dark:text-base-50 border-light-base-400 dark:border-base-700 flex w-full items-center gap-2 rounded border px-2 py-1 text-left text-sm transition-colors disabled:opacity-50',
            toggle
                ? 'hover:enabled:bg-primary-400 dark:hover:enabled:bg-primary-500 hover:enabled:text-light-base-50'
                : 'hover:enabled:bg-light-base-400 dark:hover:enabled:bg-base-700',
        ]"
    >
        <component :is="icon" v-if="icon" class="h-4.5 w-4.5" />
        <span class="sr-only">{{ title }}</span>
    </button>
</template>
