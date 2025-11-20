<script setup lang="ts">
import ChevronDown from '@/icons/ChevronDown.vue';
import { computed, inject, onMounted, ref, useId, watch } from 'vue';

const accordion = inject<{
    selected: { value: string };
    toggleAccordion: (selection: string) => void;
}>('accordionState');

const uniqueId = useId();
const headerId = `accordion-header-${uniqueId}`;
const panelId = `accordion-panel-${uniqueId}`;

const isOpen = computed(() => accordion?.selected.value === uniqueId);

function handleClick() {
    accordion?.toggleAccordion(uniqueId);
}

const panelRef = ref<HTMLElement | null>(null);

watch(
    () => accordion?.selected.value,
    () => {
        const panelElement = panelRef.value;

        if (!panelElement) {
            return;
        }

        panelElement.style.maxHeight = isOpen.value ? `${panelElement.scrollHeight}px` : '0px';
    },
    { flush: 'post' }
);

onMounted(() => {
    const panelElement = panelRef.value;

    if (!panelElement) {
        return;
    }

    panelElement.style.maxHeight = '0px';
});
</script>

<template>
    <div class="relative mb-3 py-3 last:mb-0">
        <button
            :id="headerId"
            class="w-full text-left font-semibold"
            :aria-expanded="isOpen"
            :aria-controls="panelId"
            @click="handleClick"
        >
            <div class="flex items-center justify-between">
                <span>
                    <slot name="header" />
                </span>
                <span class="px-1">
                    <ChevronDown :class="['h-5 w-5', { 'rotate-180': isOpen }]" />
                </span>
            </div>
        </button>

        <section
            :id="panelId"
            ref="panelRef"
            class="overflow-hidden transition-[max-height] duration-300 ease-in-out"
            :aria-labelledby="headerId"
        >
            <slot name="content" />
        </section>
    </div>
</template>
