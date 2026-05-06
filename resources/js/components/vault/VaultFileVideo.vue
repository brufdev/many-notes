<script setup lang="ts">
import { VaultNode } from '@/types/vault';

defineProps<{
    node: VaultNode;
}>();
</script>

<template>
    <div class="w-full px-4">
        <video class="w-full" controls>
            <source :src="node.url" />
            Your browser does not support the video tag.
        </video>
    </div>
</template>
