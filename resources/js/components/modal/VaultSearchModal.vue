<script setup lang="ts">
import VaultSearchController from '@/actions/App/Http/Controllers/VaultSearchController';
import ModelInput from '@/components/form/ModelInput.vue';
import VaultFileIcon from '@/components/vault/VaultFileIcon.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useRequest } from '@/composables/useRequest';
import { useVaultSearch } from '@/composables/useVaultSearch';
import { useVaultStore } from '@/stores/vault';
import { VaultSearchFile } from '@/types/vault';
import { formatElapsedTime, formatExtendedDate } from '@/utils/time';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    initialSearch?: string;
    onSelect: (fileId: number) => void;
}>();

const vaultStore = useVaultStore();
const { closeModal } = useModalManager();

const form = useRequest<{ search: string }>({ search: '' });

const files = ref<VaultSearchFile[]>([]);
const isLoading = ref(false);
const fileCount = computed(() => files.value.length);

const url = VaultSearchController.url({ vault: vaultStore.id! });

let requestId = 0;

function handleSubmit() {
    form.cancel();
    const currentRequest = ++requestId;

    if (!search.value) {
        hasSearched.value = false;
        files.value = [];
        isLoading.value = false;

        return;
    }

    isLoading.value = true;

    form.search = search.value;

    form.get<{ data: { files: VaultSearchFile[] } }>(url, {
        onSuccess: payload => {
            files.value = payload.data.files;
        },
        onFinish: () => {
            if (currentRequest !== requestId) {
                return;
            }

            isLoading.value = false;
        },
    });
}

function openFile() {
    if (files.value.length === 0) {
        return;
    }

    props.onSelect(files.value[selectedFile.value].id);

    closeModal();
}

const {
    search,
    hasSearched,
    selectedFile,
    listRef,
    scrollContainerRef,
    selectFile,
    selectPreviousFile,
    selectNextFile,
} = useVaultSearch(handleSubmit, fileCount);

onMounted(() => {
    search.value = props.initialSearch ?? '';
});
</script>

<template>
    <div ref="scrollContainerRef">
        <ModelInput
            v-model="search"
            name="search"
            type="text"
            placeholder="Search"
            autocomplete="off"
            autofocus
            @keydown.up.prevent.stop="selectPreviousFile"
            @keydown.down.prevent.stop="selectNextFile"
            @keydown.enter.prevent.stop="openFile"
        />
        <div class="mt-4">
            <div
                v-if="files.length > 0"
                ref="listRef"
                class="flex flex-col gap-2"
                :class="[isLoading ? 'opacity-50' : '']"
            >
                <div
                    v-for="(file, index) in files"
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
                        @click="openFile"
                    >
                        <span class="flex w-full items-center justify-between">
                            <span
                                class="flex min-w-0 flex-1 items-center gap-2 py-1"
                                :title="file.full_path"
                            >
                                <span class="flex shrink-0 items-center justify-center gap-2">
                                    <VaultFileIcon :file="file" />
                                </span>
                                <!-- eslint-disable-next-line vue/no-v-html -->
                                <span class="truncate" v-html="file.name"></span>
                            </span>
                            <span
                                class="text-light-base-700 dark:text-base-400 pl-2 text-xs"
                                :title="formatExtendedDate(file.updated_at)"
                            >
                                {{ formatElapsedTime(file.updated_at) }}
                            </span>
                        </span>
                        <!-- eslint-disable vue/no-v-html -->
                        <span
                            class="text-light-base-700 dark:text-base-200 truncate text-xs"
                            v-html="file.content"
                        ></span>
                        <!-- eslint-enable vue/no-v-html -->
                    </button>
                </div>
            </div>
            <p v-else-if="hasSearched && !isLoading">No results found</p>
        </div>
    </div>
</template>
