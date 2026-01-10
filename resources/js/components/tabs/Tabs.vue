<script setup lang="ts">
import { provide, ref, watch } from 'vue';

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const activeTab = ref<string>(props.modelValue ?? null);

function setActiveTab(tab: string) {
    activeTab.value = tab;
    emit('update:modelValue', tab);
}

provide('tabs', {
    activeTab,
    setActiveTab,
});

watch(
    () => props.modelValue,
    val => (activeTab.value = val)
);
</script>

<template>
    <div>
        <slot />
    </div>
</template>
