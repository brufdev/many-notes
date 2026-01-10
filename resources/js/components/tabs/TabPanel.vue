<script setup lang="ts">
import { computed, inject } from 'vue';

const props = defineProps<{
    name: string;
}>();

const context = inject<{
    activeTab: { value: string };
}>('tabs');

if (!context) {
    throw new Error('TabPanel must be used inside <Tabs>');
}

const isActive = computed(() => context.activeTab.value === props.name);
</script>

<template>
    <div v-if="isActive">
        <slot />
    </div>
</template>
