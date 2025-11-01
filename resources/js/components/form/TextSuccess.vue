<script setup lang="ts">
defineProps<{
    text: string;
}>();
</script>

<template>
    <p class="text-success-600 dark:text-success-500 text-sm font-medium">
        {{ text }}
    </p>
</template>
