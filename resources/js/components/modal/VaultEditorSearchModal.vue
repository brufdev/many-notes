<script setup lang="ts">
import VaultEditorSearchController from '@/actions/App/Http/Controllers/VaultEditorSearchController';
import ModelInput from '@/components/form/ModelInput.vue';
import VaultFileIcon from '@/components/vault/VaultFileIcon.vue';
import { useAxiosForm } from '@/composables/useAxiosForm';
import { useModalManager } from '@/composables/useModalManager';
import { useToast } from '@/composables/useToast';
import { useVaultSearch } from '@/composables/useVaultSearch';
import Link from '@/icons/Link.vue';
import { useVaultStore } from '@/stores/vault';
import { VaultEditorSearchFile } from '@/types/vault';
import { isValidEmail, isValidUrl } from '@/utils/link';
import { formatElapsedTime, formatExtendedDate } from '@/utils/time';
import { computed, onMounted, ref } from 'vue';

type SearchType = 'all' | 'image';

const { closeModal } = useModalManager();
const { createToast } = useToast();
const vaultStore = useVaultStore();

const props = defineProps<{
    searchType?: SearchType;
    initialUrl?: string;
    onSelect: (url: string, name: string) => void;
}>();

const files = ref<VaultEditorSearchFile[]>([]);
const isLoading = ref(false);
const resultsQuery = ref<string | null>(null);
const optionCount = computed(() => files.value.length + (hasTypedOption.value ? 1 : 0));

const {
    search,
    selectedFile,
    listRef,
    scrollContainerRef,
    selectFile,
    selectPreviousFile,
    selectNextFile,
} = useVaultSearch(() => runSearch(), optionCount);

const searchUrl = VaultEditorSearchController.url({ vault: vaultStore.id! });
const form = useAxiosForm({});

const trimmedSearch = computed(() => search.value.trim());
const resultsMatchSearch = computed(() => resultsQuery.value === trimmedSearch.value);

const isMailtoOption = computed(
    () => props.searchType !== 'image' && isValidEmail(trimmedSearch.value)
);

const skipVaultSearch = computed(() => isValidUrl(trimmedSearch.value) || isMailtoOption.value);

const hasTypedOption = computed(
    () => trimmedSearch.value !== '' && (resultsMatchSearch.value || files.value.length > 0)
);

const hint = computed(() =>
    props.searchType === 'image'
        ? 'Start typing to search images in this vault. You can also type an external image URL.'
        : 'Start typing to search files in this vault. You can also type an external URL or an email address.'
);

const typedOptionLabel = computed(() => {
    if (isMailtoOption.value) {
        return 'Email address';
    }

    if (isValidUrl(trimmedSearch.value)) {
        return props.searchType === 'image' ? 'External image' : 'External link';
    }

    return props.searchType === 'image' ? 'Internal image' : 'Internal link';
});

onMounted(() => {
    const initialUrl = props.initialUrl ?? '';

    if (isValidUrl(initialUrl)) {
        search.value = initialUrl;

        return;
    }

    if (initialUrl.startsWith('mailto:')) {
        search.value = initialUrl.slice('mailto:'.length);

        return;
    }

    if (initialUrl) {
        let filename = initialUrl.split(/[/\\]/).pop() ?? '';
        const lastDotIndex = filename.lastIndexOf('.');

        if (lastDotIndex > 0) {
            filename = filename.substring(0, lastDotIndex);
        }

        search.value = filename.replaceAll('%20', ' ');
    }
});

function runSearch() {
    const query = trimmedSearch.value;

    if (query === '' || skipVaultSearch.value) {
        files.value = [];
        resultsQuery.value = query;

        return;
    }

    isLoading.value = true;

    form.send<{ data: { files: VaultEditorSearchFile[] } }>({
        url: searchUrl,
        method: 'get',
        axiosConfig: {
            params: {
                search: query,
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
            resultsQuery.value = query;
        },
    });
}

function confirmSelection() {
    if (trimmedSearch.value === '') {
        props.onSelect('', '');
        closeModal();

        return;
    }

    if (!hasTypedOption.value) {
        return;
    }

    if (selectedFile.value >= files.value.length) {
        insertTypedOption();

        return;
    }

    insertFile(selectedFile.value);
}

function insertFile(index: number) {
    const file = files.value[index];

    if (!file) {
        return;
    }

    props.onSelect(file.full_path_encoded, file.name);
    closeModal();
}

function insertTypedOption() {
    const value = trimmedSearch.value;

    props.onSelect(
        isMailtoOption.value ? `mailto:${value}` : value,
        isMailtoOption.value ? value : ''
    );

    closeModal();
}
</script>

<template>
    <div ref="scrollContainerRef">
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
                @keydown.enter.prevent.stop="confirmSelection"
            />
            <div class="mt-4">
                <p v-if="trimmedSearch === ''" class="text-sm">
                    {{ hint }}
                </p>
                <p v-else-if="!hasTypedOption" class="text-sm">Searching...</p>
                <div v-else ref="listRef" class="flex flex-col gap-2">
                    <div
                        v-for="(file, index) in files"
                        :key="file.id"
                        class="rounded-lg p-2"
                        :class="[
                            selectedFile === index
                                ? 'bg-light-base-300 dark:bg-base-800 text-light-base-950 dark:text-base-50'
                                : 'text-light-base-700 dark:text-base-200',
                            isLoading ? 'opacity-50' : '',
                        ]"
                        @mouseenter="selectFile(index)"
                    >
                        <button
                            class="flex w-full flex-col gap-2 py-1 text-left"
                            type="button"
                            @click="insertFile(index)"
                        >
                            <span class="flex w-full items-center justify-between">
                                <span
                                    class="flex min-w-0 flex-1 items-center gap-2 py-1"
                                    :title="file.name"
                                >
                                    <span class="flex shrink-0 items-center justify-center gap-2">
                                        <VaultFileIcon :file="file" />
                                    </span>
                                    <span class="truncate">
                                        {{ file.name }}
                                    </span>
                                </span>
                                <span
                                    class="text-light-base-700 dark:text-base-400 pl-2 text-xs"
                                    :title="formatExtendedDate(file.updated_at)"
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
                    <div
                        v-if="hasTypedOption"
                        class="rounded-lg p-2"
                        :class="
                            selectedFile === files.length
                                ? 'bg-light-base-300 dark:bg-base-800 text-light-base-950 dark:text-base-50'
                                : 'text-light-base-700 dark:text-base-200'
                        "
                        @mouseenter="selectFile(files.length)"
                    >
                        <button
                            class="flex w-full flex-col gap-2 py-1 text-left"
                            type="button"
                            @click="insertTypedOption"
                        >
                            <span
                                class="flex w-full min-w-0 items-center gap-2 py-1"
                                :title="trimmedSearch"
                            >
                                <span class="flex shrink-0 items-center justify-center gap-2">
                                    <Link class="h-4 w-4 opacity-70" />
                                </span>
                                <span class="truncate">
                                    {{ trimmedSearch }}
                                </span>
                            </span>
                            <span class="text-light-base-700 dark:text-base-200 truncate text-xs">
                                {{ typedOptionLabel }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
