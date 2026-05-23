<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';

onMounted(() => {
    document.querySelector('#app')?.classList.add('flex', 'flex-col', 'h-dvh', 'print:h-auto');

    globalThis.addEventListener('dragover', prevent);
    globalThis.addEventListener('drop', prevent);
});

onUnmounted(() => {
    globalThis.removeEventListener('dragover', prevent);
    globalThis.removeEventListener('drop', prevent);
});

function prevent(event: DragEvent) {
    event.preventDefault();
}
</script>

<template>
    <slot />
</template>
