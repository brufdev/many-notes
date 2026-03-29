<script setup lang="ts">
import Fullscreen from '@/icons/Fullscreen.vue';
import { useLayoutStore } from '@/stores/layout';

const layoutStore = useLayoutStore();
</script>

<template>
    <button
        v-if="layoutStore.showToggleContentWidthButton"
        type="button"
        title="Toggle content width"
        @click="layoutStore.toggleContentWidth"
    >
        <Fullscreen class="h-5 w-5" />
    </button>
</template>
