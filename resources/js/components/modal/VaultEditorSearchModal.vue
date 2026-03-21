<script setup lang="ts">
import VaultEditorSearchController from '@/actions/App/Http/Controllers/VaultEditorSearchController';
import ModelInput from '@/components/form/ModelInput.vue';
import Submit from '@/components/form/Submit.vue';
import Tab from '@/components/tabs/Tab.vue';
import TabPanel from '@/components/tabs/TabPanel.vue';
import Tabs from '@/components/tabs/Tabs.vue';
import VaultFileIcon from '@/components/ui/VaultFileIcon.vue';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { useVaultSearch } from '@/composables/useVaultSearch';
import { useVaultStore } from '@/stores/vault';
import { VaultEditorSearchFile } from '@/types/vault';
import { formatElapsedTime } from '@/utils/time';
import { computed, onMounted, ref } from 'vue';

type SearchType = 'all' | 'image';
type TabType = 'internal' | 'external';

const { closeModal } = useModalManager();
const { createToast } = useToast();
const vaultStore = useVaultStore();

const props = defineProps<{
    searchType?: SearchType;
    initialUrl?: string;
    onSelect: (path: string) => void;
}>();

const activeTab = ref<TabType>('internal');

const files = ref<VaultEditorSearchFile[]>([]);
const isLoading = ref(false);
const fileCount = computed(() => files.value.length);

const form = useAxiosForm({});
const url = VaultEditorSearchController.url({ vault: vaultStore.id! });

const handleSubmit = () => {
    if (!search.value) {
        hasSearched.value = false;
        files.value = [];

        return;
    }

    isLoading.value = true;

    form.send<{ data: { files: VaultEditorSearchFile[] } }>({
        url: url,
        method: 'get',
        axiosConfig: {
            params: {
                search: search.value,
                searchType: props.searchType,
            },
        },
        onError: error => {
            const message = error.response?.statusText ?? 'Something went wrong';
            createToast(message, 'error');
        },
        onSuccess: payload => {
            files.value = payload.data.files;
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

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

const externalUrl = ref('');
const externalUrlError = ref(false);

onMounted(() => {
    const url = props.initialUrl ?? '';

    if (url.startsWith('http://') || url.startsWith('https://')) {
        externalUrl.value = url;
        activeTab.value = 'external';

        return;
    }

    if (url) {
        let filename = url.split(/[/\\]/).pop() ?? '';
        const lastDotIndex = filename.lastIndexOf('.');

        if (lastDotIndex > 0) {
            filename = filename.substring(0, lastDotIndex);
        }

        search.value = filename.replaceAll('%20', ' ');
    }

    activeTab.value = 'internal';
});

function insertFile() {
    if (search.value === '') {
        props.onSelect('');
        closeModal();

        return;
    }

    if (files.value.length === 0) {
        return;
    }

    const id = files.value[selectedFile.value].id;
    const file = files.value.find(f => f.id === id);

    if (!file) {
        return;
    }

    props.onSelect(file.full_path_encoded);
    closeModal();
}

function insertUrl() {
    const url = externalUrl.value.trim();
    externalUrlError.value = url !== '' && !validateUrl(url);

    if (!externalUrlError.value) {
        props.onSelect(url);
        closeModal();
    }
}

function validateUrl(url: string): boolean {
    try {
        const obj = new URL(url);
        return obj.protocol === 'http:' || obj.protocol === 'https:';
    } catch {
        return false;
    }
}
</script>

<template>
    <div ref="scrollContainerRef">
        <Tabs v-model="activeTab">
            <div class="flex gap-2 overflow-x-auto" role="tablist" aria-label="tab options">
                <Tab name="internal">Internal</Tab>
                <Tab name="external">External</Tab>
            </div>
            <TabPanel name="internal">
                <div class="py-4">
                    <ModelInput
                        v-model="search"
                        name="search"
                        type="text"
                        placeholder="Search"
                        autocomplete="off"
                        autofocus
                        @keydown.up.prevent.stop="selectPreviousFile"
                        @keydown.down.prevent.stop="selectNextFile"
                        @keydown.enter.prevent.stop="insertFile"
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
                                    @click="insertFile"
                                >
                                    <span class="flex w-full items-center justify-between">
                                        <div
                                            class="flex min-w-0 flex-1 items-center gap-2 py-1"
                                            :title="file.name"
                                        >
                                            <span
                                                class="flex shrink-0 items-center justify-center gap-2"
                                            >
                                                <VaultFileIcon :file="file" />
                                            </span>
                                            <span class="truncate">
                                                {{ file.name }}
                                            </span>
                                        </div>
                                        <span
                                            class="text-light-base-700 dark:text-base-400 pl-2 text-xs"
                                        >
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
                        <p v-else-if="hasSearched && !isLoading">No results found</p>
                    </div>
                </div>
            </TabPanel>
            <TabPanel name="external">
                <div class="py-4">
                    <ModelInput
                        v-model="externalUrl"
                        name="search"
                        type="text"
                        placeholder="Type URL"
                        autocomplete="off"
                        autofocus
                        @keydown.enter.prevent.stop="insertUrl"
                    />
                    <p
                        class="mt-2 text-xs"
                        :class="
                            externalUrlError ? 'text-error-500' : 'text-gray-500 dark:text-gray-400'
                        "
                    >
                        URLs must start with http(s)://
                    </p>
                </div>

                <div class="flex justify-end">
                    <Submit label="Save" :processing="false" @click="insertUrl" />
                </div>
            </TabPanel>
        </Tabs>
    </div>
</template>
