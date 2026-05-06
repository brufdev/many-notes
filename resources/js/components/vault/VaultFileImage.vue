<script setup lang="ts">
import { VaultNode } from '@/types/vault';

defineProps<{
    node: VaultNode;
}>();
</script>

<template>
    <div class="w-full px-4">
        <img :src="node.url" :alt="node.name" />
    </div>
</template>
