<script setup lang="ts">
import MarkdownToolbar from '@/components/editor/MarkdownToolbar.vue';
import NotificationMenu from '@/components/menu/NotificationMenu.vue';
import UserMenu from '@/components/menu/UserMenu.vue';
import VaultNodeCreateModal from '@/components/modal/VaultNodeCreateModal.vue';
import VaultSearchModal from '@/components/modal/VaultSearchModal.vue';
import VaultTree from '@/components/tree/VaultTree.vue';
import VaultFile from '@/components/vault/VaultFile.vue';
import VaultFileAudio from '@/components/vault/VaultFileAudio.vue';
import VaultFileIcon from '@/components/vault/VaultFileIcon.vue';
import VaultFileImage from '@/components/vault/VaultFileImage.vue';
import VaultFileNote from '@/components/vault/VaultFileNote.vue';
import VaultFilePdf from '@/components/vault/VaultFilePdf.vue';
import VaultFileVideo from '@/components/vault/VaultFileVideo.vue';
import VaultToggleContentWidthButton from '@/components/vault/VaultToggleContentWidthButton.vue';
import { useContentWidthPreference } from '@/composables/useContentWidthPreference';
import { useEditor } from '@/composables/useEditor';
import { useModalManager } from '@/composables/useModalManager';
import { useReloadOnPopstate } from '@/composables/useReloadOnPopstate';
import { useScreenSize } from '@/composables/useScreenSize';
import { useToast } from '@/composables/useToast';
import { useVaultActions } from '@/composables/useVaultActions';
import { useVaultTreeActions } from '@/composables/useVaultTreeActions';
import Bars3BottomLeft from '@/icons/Bars3BottomLeft.vue';
import Bars3BottomRight from '@/icons/Bars3BottomRight.vue';
import MagnifyingGlass from '@/icons/MagnifyingGlass.vue';
import Plus from '@/icons/Plus.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { index } from '@/routes/vaults';
import { useLayoutStore } from '@/stores/layout';
import { useVaultStore } from '@/stores/vault';
import { useVaultOpenedFileStore } from '@/stores/vaultOpenedFile';
import { useVaultRecentFileStore } from '@/stores/vaultRecentFile';
import { useVaultTagStore } from '@/stores/vaultTag';
import { useVaultTemplateStore } from '@/stores/vaultTemplate';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { VaultCollaborator, VaultNode, VaultOpenedFileData, VaultTag } from '@/types/vault';
import { VaultUpdated } from '@/types/vault.events';
import { VaultShowPageProps } from '@/types/vault.pages';
import { formatElapsedTime, formatExtendedDate } from '@/utils/time';
import { Head, router } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { storeToRefs } from 'pinia';
import { onMounted, provide, ref, shallowRef, watch } from 'vue';

defineOptions({ layout: AuthLayout });

const props = defineProps<VaultShowPageProps>();

const layoutStore = useLayoutStore();
const { isLeftPanelOpen, isRightPanelOpen } = storeToRefs(layoutStore);
const { toggleLeftPanel, toggleRightPanel, closePanels, syncPanelsWithScreen } = layoutStore;
const vaultStore = useVaultStore();
const vaultRecentFileStore = useVaultRecentFileStore();
const vaultOpenedFileStore = useVaultOpenedFileStore();
const vaultTreeStore = useVaultTreeStore();
const vaultTemplateStore = useVaultTemplateStore();
const vaultTagStore = useVaultTagStore();
const { openModal } = useModalManager();
const { createToast } = useToast();
const { isSmallScreen } = useScreenSize();
const vaultActions = useVaultActions();
const vaultTreeActions = useVaultTreeActions();

const mainSectionRef = ref<HTMLElement | null>(null);
useContentWidthPreference(mainSectionRef);
syncPanelsWithScreen(isSmallScreen.value);

const vaultFileKey = ref(0);
const openedFile = ref(props.openedFile ?? null);
const fileComponents = {
    note: VaultFileNote,
    image: VaultFileImage,
    pdf: VaultFilePdf,
    video: VaultFileVideo,
    audio: VaultFileAudio,
};

useReloadOnPopstate({
    onSuccess: () => {
        vaultStore.setVault(props.vault);
        vaultRecentFileStore.setRecentFiles(props.recentFiles);
        vaultTreeStore.initializeVaultTree(
            openedFile.value?.file.id ?? null,
            props.rootNodes,
            openedFile.value?.ancestors,
            openedFile.value?.ancestorsChildren
        );
        vaultTemplateStore.setTemplates(props.templateNodes);
    },
});

const editorContext = shallowRef<ReturnType<typeof useEditor> | null>(null);
provide('editorContext', editorContext);

onMounted(() => {
    vaultStore.setVault(props.vault);
    vaultRecentFileStore.setRecentFiles(props.recentFiles);
    vaultTreeStore.initializeVaultTree(
        openedFile.value?.file.id ?? null,
        props.rootNodes,
        openedFile.value?.ancestors,
        openedFile.value?.ancestorsChildren
    );
    vaultTemplateStore.setTemplates(props.templateNodes);
});

watch(
    () => props.openedFile,
    openedFileProp => {
        openedFile.value = openedFileProp ?? null;
        vaultOpenedFileStore.set(
            openedFile.value?.links,
            openedFile.value?.backlinks,
            openedFile.value?.tags
        );

        if (openedFile.value) {
            vaultFileKey.value++;

            vaultTreeStore.handleFileOpened(
                openedFile.value.file.id,
                openedFile.value?.ancestors ?? [],
                openedFile.value?.ancestorsChildren ?? {}
            );
        }
    },
    { immediate: true }
);

watch(
    () => props.recentFiles,
    files => vaultRecentFileStore.setRecentFiles(files),
    { immediate: true }
);

watch(
    () => props.tags,
    tags => vaultTagStore.setTags(tags),
    { immediate: true }
);

watch(isSmallScreen, value => {
    syncPanelsWithScreen(value);
});

useEcho<{ data: VaultUpdated }>(`Vault.${props.vault.id}`, 'VaultUpdatedEvent', payload => {
    vaultStore.updateVault(payload.data);
});

useEcho(`Vault.${props.vault.id}`, 'VaultDeletedEvent', () => {
    router.visit(index.url(), {
        replace: true,
        fresh: true,
        onSuccess: () => {
            createToast('Vault deleted', 'warning');
        },
    });
});

useEcho<{ data: VaultTag[] }>(`Vault.${props.vault.id}`, 'VaultTagListUpdatedEvent', payload => {
    vaultTagStore.setTags(payload.data);
});

useEcho<{ data: VaultNode }>(`Vault.${props.vault.id}`, 'VaultNodeCreatedEvent', payload => {
    vaultTreeStore.handleNodeSaved(payload.data);
    vaultRecentFileStore.upsertRecentFile(payload.data);
});

useEcho<{ data: VaultNode }>(`Vault.${props.vault.id}`, 'VaultNodeUpdatedEvent', payload => {
    vaultTreeActions.handleNodeUpdated(payload.data);
    vaultRecentFileStore.upsertRecentFile(payload.data);

    if (openedFile.value?.file.id === payload.data.id) {
        if (openedFile.value?.file.name !== payload.data.name) {
            openedFile.value.file.name = payload.data.name;
        }

        if (openedFile.value?.file.parent_id !== payload.data.parent_id) {
            openedFile.value.file.parent_id = payload.data.parent_id;
        }

        if (
            payload.data.type === 'note' &&
            openedFile.value?.file.content !== payload.data.content
        ) {
            openedFile.value.file.content = payload.data.content;
            editorContext.value?.setContent(payload.data.content ?? '');
        }
    }
});

useEcho<{ data: VaultOpenedFileData }>(
    `Vault.${props.vault.id}`,
    'VaultOpenedFileDataUpdatedEvent',
    payload => {
        if (openedFile.value?.file.id !== payload.data.file.id) {
            return;
        }

        vaultOpenedFileStore.set(payload.data.links, payload.data.backlinks, payload.data.tags);
    }
);

useEcho<{ data: { deleted_ids: number[] } }>(
    `Vault.${props.vault.id}`,
    'VaultNodeDeletedEvent',
    payload => {
        vaultActions.handleNodesDeleted(payload.data.deleted_ids);
    }
);

useEcho<{ data: VaultCollaborator }>(
    `Vault.${props.vault.id}`,
    'VaultCollaborationCreatedEvent',
    ({ data }) => {
        vaultStore.addCollaborator(data);
    }
);

useEcho<{ data: VaultCollaborator }>(
    `Vault.${props.vault.id}`,
    'VaultCollaborationAcceptedEvent',
    ({ data }) => {
        vaultStore.updateCollaborator(data);
    }
);

useEcho<{ data: { user_id: number } }>(
    `Vault.${props.vault.id}`,
    'VaultCollaborationDeletedEvent',
    ({ data }) => {
        vaultStore.removeCollaborator(data.user_id);
    }
);
</script>

<template>
    <Head :title="vault.name ?? ''" />

    <Teleport defer to="#app-header">
        <div class="flex items-center gap-3">
            <button
                class="hover:text-light-base-950 dark:hover:text-base-50"
                type="button"
                @click="toggleLeftPanel(isSmallScreen)"
            >
                <Bars3BottomLeft class="h-5 w-5" />
            </button>
            <button
                class="hover:text-light-base-950 dark:hover:text-base-50"
                type="button"
                @click="
                    openModal(VaultSearchModal, {
                        title: 'Search',
                        top: true,
                        onSelect: (fileId: number) => vaultActions.openFile(fileId),
                    })
                "
            >
                <MagnifyingGlass class="h-5 w-5" />
            </button>
        </div>
        <div class="flex items-center gap-3">
            <NotificationMenu />
            <UserMenu />
            <button
                class="hover:text-light-base-950 dark:hover:text-base-50"
                type="button"
                @click="toggleRightPanel(isSmallScreen)"
            >
                <Bars3BottomRight class="h-5 w-5" />
            </button>
        </div>
    </Teleport>

    <Transition
        enter-active-class="ease-out duration-300"
        leave-active-class="ease-in duration-200"
    >
        <div
            v-if="isSmallScreen && (isLeftPanelOpen || isRightPanelOpen)"
            class="bg-light-base-50 dark:bg-base-900 fixed inset-0 z-20 opacity-50"
            @click="closePanels"
        ></div>
    </Transition>

    <aside
        class="bg-light-base-200 dark:bg-base-800 absolute top-0 bottom-0 left-0 z-30 w-[80%] max-w-[300px] overflow-y-auto rounded-r transition-all duration-300 ease-in-out lg:static lg:max-w-[300px] print:hidden"
        :class="{
            '-translate-x-full': isSmallScreen && !isLeftPanelOpen,
            'translate-x-0': isSmallScreen && isLeftPanelOpen,
            'lg:w-0 lg:min-w-0': !isLeftPanelOpen,
            'lg:w-[20%] lg:min-w-[240px]': isLeftPanelOpen,
        }"
    >
        <VaultTree
            :vault-id="props.vault.id"
            :vault-name="vault.name"
            :vault-created-by="props.vault.created_by"
        />
    </aside>

    <section
        ref="mainSectionRef"
        class="h-full max-w-full flex-1 transition-all duration-300 ease-in-out"
    >
        <div
            class="mx-auto flex h-full w-full flex-col transition-all duration-300 ease-in-out"
            :class="layoutStore.isContentWidthFull ? 'max-w-full' : 'max-w-[48rem]'"
        >
            <VaultFile
                v-if="openedFile"
                :key="vaultFileKey"
                :node="openedFile.file"
                @close="vaultActions.closeFile"
                @name-updated="openedFile.file.name = $event"
            >
                <template v-if="openedFile.file.type === 'note'" #toolbar>
                    <MarkdownToolbar :vault-id="props.vault.id" :node-id="openedFile.file.id" />
                </template>
                <component
                    :is="fileComponents[openedFile.file.type]"
                    v-if="openedFile.file.type !== 'folder'"
                    :node="openedFile.file"
                    @content-updated="openedFile.file.content = $event"
                />
            </VaultFile>
            <div v-else class="flex h-full w-full flex-col">
                <div class="flex items-center justify-between gap-2 p-4">
                    <div class="text-lg font-semibold">Recent files</div>
                    <div class="flex items-center gap-2">
                        <VaultToggleContentWidthButton />

                        <button
                            type="button"
                            title="New note"
                            @click="
                                openModal(VaultNodeCreateModal, {
                                    title: 'New note',
                                    vaultId: props.vault.id,
                                    parentId: null,
                                    isFile: true,
                                })
                            "
                        >
                            <Plus class="h-5 w-5" />
                        </button>
                    </div>
                </div>
                <div class="-mt-2 flex w-full flex-grow flex-col overflow-y-auto px-4">
                    <template v-for="file in vaultRecentFileStore.recentFiles" :key="file.id">
                        <button
                            class="border-light-base-300 dark:border-base-500 hover:text-primary-600 dark:hover:text-primary-300 flex w-full flex-col gap-2 border-b pt-2 pb-4 text-start last:border-b-0"
                            type="button"
                            @click="vaultActions.openFile(file.id)"
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
                    </template>
                </div>
            </div>
        </div>
    </section>

    <aside
        class="bg-light-base-200 dark:bg-base-800 absolute top-0 right-0 bottom-0 z-30 w-[80%] max-w-[300px] overflow-y-auto rounded-l transition-all duration-300 ease-in-out lg:static lg:max-w-[300px] print:hidden"
        :class="{
            'translate-x-full': isSmallScreen && !isRightPanelOpen,
            'translate-x-0': isSmallScreen && isRightPanelOpen,
            'lg:w-0 lg:min-w-0': !isRightPanelOpen,
            'lg:w-[20%] lg:min-w-[240px]': isRightPanelOpen,
        }"
    >
        <div class="flex-co flex h-full w-full overflow-y-auto text-sm">
            <div v-if="openedFile" class="flex w-full flex-col">
                <div class="flex w-full flex-col">
                    <div class="truncate p-4 text-lg font-semibold">Links</div>
                    <div class="flex flex-col gap-2 px-4">
                        <template v-if="vaultOpenedFileStore.links.length > 0">
                            <button
                                v-for="link in vaultOpenedFileStore.links"
                                :key="link.id"
                                class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                @click="vaultActions.openFile(link.id)"
                            >
                                <span class="flex w-full items-center justify-between">
                                    <span class="flex-grow truncate text-left" :title="link.name">
                                        {{ link.name }}
                                    </span>
                                    <span
                                        class="text-light-base-700 dark:text-base-400 pl-2 text-xs"
                                    >
                                        {{ link.total }}
                                    </span>
                                </span>
                            </button>
                        </template>
                        <p v-else>No links found</p>
                    </div>
                </div>
                <div class="flex w-full flex-col">
                    <div class="truncate p-4 text-lg font-semibold">Backlinks</div>
                    <div class="flex flex-col gap-2 px-4">
                        <template v-if="vaultOpenedFileStore.backlinks.length > 0">
                            <button
                                v-for="link in vaultOpenedFileStore.backlinks"
                                :key="link.id"
                                class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                @click="vaultActions.openFile(link.id)"
                            >
                                <span class="flex w-full items-center justify-between">
                                    <span class="flex-grow truncate text-left" :title="link.name">
                                        {{ link.name }}
                                    </span>
                                    <span
                                        class="text-light-base-700 dark:text-base-400 pl-2 text-xs"
                                    >
                                        {{ link.total }}
                                    </span>
                                </span>
                            </button>
                        </template>
                        <p v-else>No backlinks found</p>
                    </div>
                </div>
                <div class="flex w-full flex-col">
                    <div class="truncate p-4 text-lg font-semibold">Tags</div>
                    <div class="flex flex-col gap-2 px-4">
                        <template v-if="vaultOpenedFileStore.tags.length > 0">
                            <button
                                v-for="tag in vaultOpenedFileStore.tags"
                                :key="tag.id"
                                class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                                @click="
                                    openModal(VaultSearchModal, {
                                        title: 'Search',
                                        top: true,
                                        initialSearch: `tag:${tag.name}`,
                                        onSelect: (fileId: number) => vaultActions.openFile(fileId),
                                    })
                                "
                            >
                                <span class="flex w-full items-center justify-between">
                                    <span class="flex-grow truncate text-left" :title="tag.name">
                                        #{{ tag.name }}
                                    </span>
                                    <span
                                        class="text-light-base-700 dark:text-base-400 pl-2 text-xs"
                                    >
                                        {{ tag.total }}
                                    </span>
                                </span>
                            </button>
                        </template>
                        <p v-else>No tags found</p>
                    </div>
                </div>
            </div>
            <div v-else class="flex w-full flex-col">
                <div class="truncate p-4 text-lg font-semibold">Tags</div>
                <div class="flex flex-col gap-2 px-4">
                    <template v-if="vaultTagStore.tags.length > 0">
                        <button
                            v-for="tag in vaultTagStore.tags"
                            :key="tag.id"
                            class="text-primary-400 dark:text-primary-500 hover:text-primary-300 dark:hover:text-primary-600"
                            @click="
                                openModal(VaultSearchModal, {
                                    title: 'Search',
                                    top: true,
                                    initialSearch: `tag:${tag.name}`,
                                    onSelect: (fileId: number) => vaultActions.openFile(fileId),
                                })
                            "
                        >
                            <span class="flex w-full items-center justify-between">
                                <span class="flex-grow truncate text-left" :title="tag.name">
                                    #{{ tag.name }}
                                </span>
                                <span class="text-light-base-700 dark:text-base-400 pl-2 text-xs">
                                    {{ tag.total }}
                                </span>
                            </span>
                        </button>
                    </template>
                    <div v-else class="px-3 text-sm">No tags found</div>
                </div>
            </div>
        </div>
    </aside>
</template>
