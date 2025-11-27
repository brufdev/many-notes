<script setup lang="ts">
import { onMounted } from 'vue';

onMounted(() => {
    document.querySelector('#app')?.classList.add('flex', 'flex-col', 'h-dvh');
});
</script>

<template>
    <slot />
</template>
