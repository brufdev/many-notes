<script setup lang="ts">
import { useVaultTreeStore } from '@/stores/vaultTree';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import VaultTreeNode from './VaultTreeNode.vue';

const props = defineProps<{
    parentId: number;
    depth: number;
    expanded: boolean;
}>();

const tree = useVaultTreeStore();
const children = computed(() => tree.getChildren(props.parentId));

const container = ref<HTMLElement | null>(null);
const maxHeight = ref('0px');

let observer: ResizeObserver | null = null;

onMounted(() => {
    if (!container.value) return;

    observer = new ResizeObserver(() => {
        if (props.expanded && container.value) {
            maxHeight.value = `${container.value.scrollHeight}px`;
        }
    });

    observer.observe(container.value);
});

onBeforeUnmount(() => {
    observer?.disconnect();
});

watch(
    () => props.expanded,
    open => {
        if (!container.value) return;

        maxHeight.value = open ? `${container.value.scrollHeight}px` : '0px';
    },
    { immediate: true }
);
</script>

<template>
    <div
        class="border-light-base-400 dark:border-base-500 ml-3 overflow-hidden border-l-2 pl-2 transition-[max-height] duration-200 ease-in-out"
        :style="{ maxHeight }"
    >
        <div ref="container">
            <VaultTreeNode v-for="id in children" :key="id" :node-id="id" :depth="depth" />
        </div>
    </div>
</template>
