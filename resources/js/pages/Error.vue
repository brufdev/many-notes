<script setup lang="ts">
import { TextLink } from '@/components/form';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

defineOptions({ layout: GuestLayout });

const props = defineProps<{
    status: number;
}>();

const titles: Record<number, string> = {
    403: 'Forbidden',
    404: 'Page not found',
    419: 'Page expired',
    500: 'Server error',
    503: 'Service unavailable',
};

const descriptions: Record<number, string> = {
    403: 'You do not have permission to access this page.',
    404: "The page you're looking for doesn't exist.",
    419: 'Your session has expired. Please sign in again.',
    500: 'Something went wrong on our end.',
    503: 'The app is temporarily unavailable. Please try again shortly.',
};

const title = computed(() => titles[props.status] ?? 'Something went wrong');
const description = computed(() => descriptions[props.status] ?? 'An unexpected error occurred.');
</script>

<template>
    <Head :title="title" />

    <div class="flex flex-col gap-2 text-center">
        <div class="text-3xl font-semibold">{{ status }}</div>
        <div class="text-lg">{{ title }}</div>
    </div>

    <div class="text-center text-sm">
        {{ description }}
    </div>

    <div class="text-center text-sm">
        <TextLink href="/" label="Back to Many Notes" />
    </div>
</template>
