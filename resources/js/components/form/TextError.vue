<script setup lang="ts">
defineProps<{
    text: string;
}>();
</script>

<template>
    <p class="text-error-600 dark:text-error-500 text-sm font-medium">
        {{ text }}
    </p>
</template>
