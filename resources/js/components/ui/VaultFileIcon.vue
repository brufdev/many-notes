<script setup lang="ts">
import FileAudio from '@/icons/FileAudio.vue';
import FileImage from '@/icons/FileImage.vue';
import FileMarkdown from '@/icons/FileMarkdown.vue';
import FilePDF from '@/icons/FilePDF.vue';
import FileVideo from '@/icons/FileVideo.vue';

defineProps<{
    file: {
        type: string;
        extension: string | null;
    };
}>();
</script>

<template>
    <FileMarkdown v-if="file.extension === 'md'" class="h-4 w-4 opacity-70" />
    <FileAudio v-else-if="file.type === 'audio'" class="h-4 w-4 opacity-70" />
    <FileImage v-else-if="file.type === 'image'" class="h-4 w-4 opacity-70" />
    <FilePDF v-else-if="file.type === 'pdf'" class="h-4 w-4 opacity-70" />
    <FileVideo v-else-if="file.type === 'video'" class="h-4 w-4 opacity-70" />
</template>
