<script setup lang="ts">
import { destroy } from '@/actions/App/Http/Controllers/VaultNodeController';
import Menu from '@/components/menu/Menu.vue';
import MenuItem from '@/components/menu/MenuItem.vue';
import AxiosFormConfirmationModal from '@/components/modal/AxiosFormConfirmationModal.vue';
import VaultFilesImportModal from '@/components/modal/VaultFilesImportModal.vue';
import VaultNodeCreateModal from '@/components/modal/VaultNodeCreateModal.vue';
import VaultNodeEditModal from '@/components/modal/VaultNodeEditModal.vue';
import { useModalManager } from '@/composables/useModalManager';
import { useScreenSize } from '@/composables/useScreenSize';
import { useVaultActions } from '@/composables/useVaultActions';
import { useVaultTreeActions } from '@/composables/useVaultTreeActions';
import ArrowUpTray from '@/icons/ArrowUpTray.vue';
import ChevronDown from '@/icons/ChevronDown.vue';
import DocumentDuplicate from '@/icons/DocumentDuplicate.vue';
import DocumentPlus from '@/icons/DocumentPlus.vue';
import EllipsisVertical from '@/icons/EllipsisVertical.vue';
import FileAudio from '@/icons/FileAudio.vue';
import FileImage from '@/icons/FileImage.vue';
import FileMarkdown from '@/icons/FileMarkdown.vue';
import FilePDF from '@/icons/FilePDF.vue';
import FileVideo from '@/icons/FileVideo.vue';
import FolderPlus from '@/icons/FolderPlus.vue';
import PencilSquare from '@/icons/PencilSquare.vue';
import Spinner from '@/icons/Spinner.vue';
import Trash from '@/icons/Trash.vue';
import { useLayoutStore } from '@/stores/layout';
import { useVaultStore } from '@/stores/vault';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { VaultNodeTreeDropIndicator, VaultNodeTreeItem } from '@/types/vault';
import { VaultShowPageProps } from '@/types/vault.pages';
import { usePage } from '@inertiajs/vue3';
import { computed, inject, Ref } from 'vue';
import VaultTreeChildren from './VaultTreeChildren.vue';

interface VaultTreeNodeProps {
    nodeId: number;
    depth: number;
}

const props = defineProps<VaultTreeNodeProps>();

const page = usePage<VaultShowPageProps>();
const layoutStore = useLayoutStore();
const { isSmallScreen } = useScreenSize();
const { openModal } = useModalManager();
const vaultStore = useVaultStore();
const vaultTreeStore = useVaultTreeStore();
const vaultActions = useVaultActions();
const vaultTreeActions = useVaultTreeActions();

const node = computed(() => {
    const node = vaultTreeStore.getNodeById(props.nodeId);

    if (!node) {
        throw new Error('Node not found');
    }

    return node;
});

const isTemplateFolder = computed(() => vaultStore.isTemplateFolder(props.nodeId));
const isExpanded = computed(() => vaultTreeStore.isFolderExpanded(props.nodeId));
const isSelected = computed(() => vaultTreeStore.getSelectedFileId() === props.nodeId);
const isLoading = computed(() => vaultTreeStore.isFolderLoading(props.nodeId));

function handleClick() {
    if (node.value.is_file) {
        if (isSmallScreen.value) {
            layoutStore.closePanels();
        }

        vaultActions.openFile(node.value.id);
    } else {
        vaultTreeActions.toggleFolder(node.value.id);
    }
}

const vaultTreeDragAndDrop = inject<{
    draggingNodeId: Ref<number | null>;
    dropIndicator: Ref<VaultNodeTreeDropIndicator | null>;
    onDragStart: (event: DragEvent, id: number) => void;
    onDragEnd: () => void;
    onDragLeave: (node: VaultNodeTreeItem) => void;
    onDragOverNode: (event: DragEvent, node: VaultNodeTreeItem) => void;
    onDrop: (event: DragEvent) => void;
}>('vaultTreeDragAndDrop')!;

const isValidDropInside = computed(() => {
    return (
        vaultTreeDragAndDrop.dropIndicator.value?.type === 'inside' &&
        vaultTreeDragAndDrop.dropIndicator.value.targetId === node.value.id
    );
});

const isValidDropAfter = computed(() => {
    return (
        vaultTreeDragAndDrop.dropIndicator.value?.type === 'after' &&
        vaultTreeDragAndDrop.dropIndicator.value.targetId === node.value.id
    );
});
</script>

<template>
    <div
        class="relative"
        draggable="true"
        @dragstart.stop="vaultTreeDragAndDrop.onDragStart($event, node.id)"
        @dragend="vaultTreeDragAndDrop.onDragEnd()"
        @dragover.prevent="vaultTreeDragAndDrop.onDragOverNode($event, node)"
        @drop="vaultTreeDragAndDrop.onDrop"
    >
        <div
            class="group relative flex cursor-pointer items-center rounded pl-1 transition-colors"
            :class="[
                isSelected
                    ? 'bg-primary-400 dark:bg-primary-500 text-light-base-50'
                    : 'text-light-base-950 dark:text-base-50 hover:bg-light-base-400 dark:hover:bg-base-700',
                isValidDropInside ? 'bg-light-base-400 dark:bg-base-700' : '',
            ]"
        >
            <div
                class="flex min-w-0 flex-1 items-center gap-2 py-1"
                :title="node.name"
                @click="handleClick"
            >
                <span class="flex shrink-0 items-center justify-center gap-2">
                    <template v-if="!node.is_file">
                        <Spinner v-if="isLoading" class="h-4 w-4 animate-spin opacity-70" />
                        <ChevronDown
                            v-else
                            class="h-4 w-4 opacity-70"
                            :class="{ 'rotate-270': !isExpanded }"
                        />
                    </template>
                    <FileMarkdown v-else-if="node.extension === 'md'" class="h-4 w-4 opacity-70" />
                    <FileAudio v-else-if="node.type === 'audio'" class="h-4 w-4 opacity-70" />
                    <FileImage v-else-if="node.type === 'image'" class="h-4 w-4 opacity-70" />
                    <FilePDF v-else-if="node.type === 'pdf'" class="h-4 w-4 opacity-70" />
                    <FileVideo v-else-if="node.type === 'video'" class="h-4 w-4 opacity-70" />
                </span>
                <span class="truncate">
                    {{ node.name }}
                </span>
            </div>
            <Menu type="dropdown">
                <template #trigger>
                    <button
                        class="flex shrink-0 items-center py-1 opacity-0 transition-opacity group-hover:opacity-100 focus-visible:opacity-100 [@media(hover:none)]:opacity-100"
                    >
                        <EllipsisVertical class="h-5 w-5" />
                    </button>
                </template>
                <template #default="{ closeMenu }">
                    <div class="min-w-[10rem]">
                        <MenuItem
                            v-if="!node.is_file"
                            label="New note"
                            :icon="DocumentPlus"
                            @click="
                                closeMenu();
                                openModal(VaultNodeCreateModal, {
                                    title: 'New note',
                                    vaultId: page.props.vault.id,
                                    parentId: node.id,
                                    isFile: true,
                                });
                            "
                        />
                        <MenuItem
                            v-if="!node.is_file"
                            label="New folder"
                            :icon="FolderPlus"
                            @click="
                                closeMenu();
                                openModal(VaultNodeCreateModal, {
                                    title: 'New folder',
                                    vaultId: page.props.vault.id,
                                    parentId: node.id,
                                    isFile: false,
                                });
                            "
                        />
                        <MenuItem
                            v-if="!node.is_file"
                            label="Import files"
                            :icon="ArrowUpTray"
                            @click="
                                closeMenu();
                                openModal(VaultFilesImportModal, {
                                    title: 'Import files',
                                    vaultId: page.props.vault.id,
                                    parentId: node.id,
                                });
                            "
                        />
                        <MenuItem
                            :label="node.is_file ? 'Edit file' : 'Edit folder'"
                            :icon="PencilSquare"
                            @click="
                                closeMenu();
                                openModal(VaultNodeEditModal, {
                                    title: node.is_file ? 'Edit file' : 'Edit folder',
                                    id: node.id,
                                    vaultId: page.props.vault.id,
                                    isFile: node.is_file,
                                    name: node.name,
                                });
                            "
                        />
                        <MenuItem
                            v-if="!node.is_file"
                            label="Template folder"
                            :label-class="
                                isTemplateFolder ? 'text-success-600 dark:text-success-500' : ''
                            "
                            :icon="DocumentDuplicate"
                            @click="
                                closeMenu();
                                vaultTreeActions.setTemplateFolder(node.id);
                            "
                        />
                        <MenuItem
                            label="Delete"
                            :icon="Trash"
                            @click="
                                closeMenu();
                                openModal(AxiosFormConfirmationModal, {
                                    title: node.is_file ? 'Delete file' : 'Delete folder',
                                    url: destroy.url({
                                        vault: page.props.vault.id,
                                        node: node.id,
                                    }),
                                    method: 'delete',
                                    content: node.is_file
                                        ? 'Are you sure you want to delete this file?'
                                        : 'Are you sure you want to delete this folder?',
                                    successMessage: node.is_file
                                        ? 'File deleted'
                                        : 'Folder deleted',
                                    onSuccess: (response: { data: { deleted_ids: number[] } }) => {
                                        vaultTreeActions.handleNodesDeleted(
                                            response.data.deleted_ids,
                                            false
                                        );
                                    },
                                });
                            "
                        />
                    </div>
                </template>
            </Menu>
        </div>
        <VaultTreeChildren
            v-if="!node.is_file && isExpanded"
            :parent-id="nodeId"
            :depth="depth + 1"
            :expanded="isExpanded"
        />
        <div
            v-if="isValidDropAfter"
            class="border-light-base-400 dark:border-base-700 absolute right-0 bottom-0 left-0 border"
        />
    </div>
</template>
