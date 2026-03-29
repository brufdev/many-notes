<script setup lang="ts">
import VaultFileIcon from '@/components/vault/VaultFileIcon.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useVaultSearch } from '@/composables/useVaultSearch';
import { useVaultTemplateStore } from '@/stores/vaultTemplate';
import { formatElapsedTime } from '@/utils/time';
import { storeToRefs } from 'pinia';
import { computed, onMounted, onUnmounted } from 'vue';

const { closeModal } = useModalManager();
const vaultTemplateStore = useVaultTemplateStore();

const props = defineProps<{
    onSelect: (id: number) => void;
}>();

const { templates } = storeToRefs(vaultTemplateStore);
const templatesCount = computed(() => templates.value.length);

const {
    selectedFile,
    scrollContainerRef,
    listRef,
    selectFile,
    selectPreviousFile,
    selectNextFile,
} = useVaultSearch(() => {}, templatesCount);

function insertFile() {
    if (templates.value.length === 0) {
        return;
    }

    const file = templates.value[selectedFile.value];

    if (!file) {
        return;
    }

    props.onSelect(file.id);

    closeModal();
}

function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'ArrowUp') {
        event.preventDefault();
        event.stopPropagation();

        selectPreviousFile();
    } else if (event.key === 'ArrowDown') {
        event.preventDefault();
        event.stopPropagation();

        selectNextFile();
    } else if (event.key === 'Enter') {
        event.preventDefault();
        event.stopPropagation();

        insertFile();
    }
}

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div ref="scrollContainerRef">
        <div class="py-4">
            <div v-if="templates.length > 0" ref="listRef" class="flex flex-col gap-2">
                <div
                    v-for="(file, index) in templates"
                    :key="file.id"
                    class="rounded-lg p-2"
                    :class="
                        selectedFile === index
                            ? 'bg-light-base-300 dark:bg-base-800 text-light-base-950 dark:text-base-50'
                            : 'text-light-base-700 dark:text-base-200'
                    "
                    @mouseenter="selectFile(index)"
                >
                    <button
                        class="flex w-full flex-col gap-2 py-1 text-left"
                        type="button"
                        @click="insertFile"
                    >
                        <span class="flex w-full items-center justify-between">
                            <div
                                class="flex min-w-0 flex-1 items-center gap-2 py-1"
                                :title="file.name"
                            >
                                <span class="flex shrink-0 items-center justify-center gap-2">
                                    <VaultFileIcon :file="file" />
                                </span>
                                <span class="truncate">
                                    {{ file.name }}
                                </span>
                            </div>
                            <span class="text-light-base-700 dark:text-base-400 pl-2 text-xs">
                                {{ formatElapsedTime(file.updated_at) }}
                            </span>
                        </span>
                        <span
                            class="text-light-base-700 dark:text-base-200 truncate text-xs"
                            :title="file.full_path"
                        >
                            {{ file.full_path }}
                        </span>
                    </button>
                </div>
            </div>
            <p v-else>No results found</p>
        </div>
    </div>
</template>
