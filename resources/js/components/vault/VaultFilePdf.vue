<script setup lang="ts">
import { VaultNode } from '@/types/vault';

defineProps<{
    node: VaultNode;
}>();
</script>

<template>
    <div class="h-full w-full px-4">
        <object class="h-full w-full" type="application/pdf" :data="node.url" />
    </div>
</template>
