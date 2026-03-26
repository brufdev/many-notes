<script setup lang="ts">
import { type Component } from 'vue';

withDefaults(
    defineProps<{
        icon: Component;
        iconRotate?: boolean;
        title?: string;
    }>(),
    {
        iconRotate: false,
        title: '',
    }
);
</script>

<template>
    <button
        type="button"
        :title="title"
        class="hover:bg-light-base-400 dark:hover:bg-base-700 text-light-base-950 dark:text-base-50 flex items-center gap-2 rounded px-2 py-1 text-left text-sm transition-colors"
    >
        <component :is="icon" v-if="icon" :class="['h-4.5 w-4.5', iconRotate && 'rotate-180']" />
        <span class="sr-only">{{ title }}</span>
    </button>
</template>
