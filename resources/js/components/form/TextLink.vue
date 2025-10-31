<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    href: string;
    label: string;
}>();
</script>

<template>
    <Link
        :href="href"
        class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
    >
        {{ label }}
    </Link>
</template>
