<script setup lang="ts">
import Spinner from '@/icons/Spinner.vue';
import { useLayoutStore } from '@/stores/layout';

const layoutStore = useLayoutStore();
</script>

<template>
    <span v-if="layoutStore.isVaultNodeUpdating" class="flex items-center">
        <Spinner class="h-4 w-4 animate-spin" />
    </span>
</template>
