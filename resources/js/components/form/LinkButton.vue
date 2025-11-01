<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    href: string;
    primary?: boolean;
    full?: boolean;
}>();
</script>

<template>
    <Link
        :href="href"
        :class="[
            'flex items-center gap-2 rounded-md border px-2 py-1.5',
            primary
                ? 'border-primary-300 dark:border-primary-600 bg-primary-400 dark:bg-primary-500 hover:bg-primary-300 dark:hover:bg-primary-600 text-light-base-50'
                : 'border-light-base-400 dark:border-base-700 bg-light-base-300 dark:bg-base-500 hover:bg-light-base-400 dark:hover:bg-base-700 text-light-base-950 dark:text-base-50',
            full ? 'w-full justify-center' : '',
        ]"
    >
        <slot />
    </Link>
</template>
