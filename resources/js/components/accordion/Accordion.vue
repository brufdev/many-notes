<script setup lang="ts">
import { provide, ref } from 'vue';

const selected = ref<string>('');

const toggleAccordion = (selection: string): void => {
    selected.value = selected.value === selection ? '' : selection;
};

provide('accordionState', {
    selected,
    toggleAccordion,
});
</script>

<template>
    <div class="space-y-2">
        <slot />
    </div>
</template>
