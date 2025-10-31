<script setup lang="ts">
import Spinner from '@/icons/Spinner.vue';

defineProps<{
    label: string;
    processing: boolean;
}>();
</script>

<template>
    <button
        class="bg-primary-400 dark:bg-primary-500 hover:bg-primary-300 dark:hover:bg-primary-600 text-light-base-50 focus-visible:outline-light-base-600 dark:focus-visible:outline-base-400 relative flex w-full justify-center rounded-md px-3 py-1.5 text-sm leading-6 font-semibold shadow-sm focus-visible:outline focus-visible:outline-1 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-75"
        type="submit"
    >
        {{ label }}

        <span v-if="processing" class="absolute top-0 right-0 bottom-0 flex items-center pr-4">
            <Spinner class="h-5 w-5 animate-spin" />
        </span>
    </button>
</template>
