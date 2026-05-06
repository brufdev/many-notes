<script setup lang="ts">
import { VaultNode } from '@/types/vault';

defineProps<{
    node: VaultNode;
}>();
</script>

<template>
    <div class="w-full px-4">
        <audio class="w-full" controls>
            <source :src="node.url" />
            Your browser does not support the audio tag.
        </audio>
    </div>
</template>
