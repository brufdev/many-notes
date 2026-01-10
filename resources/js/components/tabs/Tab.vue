<script setup lang="ts">
import { computed, inject } from 'vue';

const props = defineProps<{
    name: string;
}>();

const context = inject<{
    activeTab: { value: string };
    setActiveTab: (tab: string) => void;
}>('tabs');

if (!context) {
    throw new Error('Tab must be used inside <Tabs>');
}

const isActive = computed(() => context.activeTab.value === props.name);

function activate() {
    context!.setActiveTab(props.name);
}
</script>

<template>
    <button
        class="h-min"
        type="button"
        role="tab"
        :aria-controls="`tabpanel${name}`"
        :aria-selected="isActive"
        :tabindex="isActive ? '0' : '-1'"
        :class="isActive ? 'border-b-2' : 'hover:border-b-2'"
        @click="activate"
    >
        <slot :active="isActive" />
    </button>
</template>
