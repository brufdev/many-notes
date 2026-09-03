<script setup lang="ts">
import Menu from '@/components/menu/Menu.vue';
import MenuItem from '@/components/menu/MenuItem.vue';
import {
    VaultCollaborationModal,
    VaultEditModal,
    VaultFilesImportModal,
    VaultNodeCreateModal,
} from '@/components/modal';
import { useModalManager, useVaultActions } from '@/composables';
import {
    ArrowUpTray,
    Bars3,
    DocumentPlus,
    FolderPlus,
    PencilSquare,
    Spinner,
    UserGroup,
} from '@/icons';
import { useLayoutStore } from '@/stores/layout';
import { useVaultStore } from '@/stores/vault';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { VaultNode, VaultNodeTreeDropIndicator } from '@/types/vault';
import { VaultUpdated } from '@/types/vault.events';
import { usePage } from '@inertiajs/vue3';
import { computed, provide, ref } from 'vue';
import VaultTreeNode from './VaultTreeNode.vue';

const props = defineProps<{
    vaultId: number;
}>();

const page = usePage();
const { openModal } = useModalManager();
const layoutStore = useLayoutStore();
const vaultStore = useVaultStore();
const vaultActions = useVaultActions();
const vaultTreeStore = useVaultTreeStore();

const userId = computed(() => page.props.app?.user?.id);
const children = computed(() => vaultTreeStore.getChildren(null));

const draggingNodeId = ref<number | null>(null);
const dropIndicator = ref<VaultNodeTreeDropIndicator>(null);
const isValidDropAfter = computed(() => dropIndicator.value?.type === 'root');

function canDrop(draggedId: number, target: VaultNode): boolean {
    const node = vaultTreeStore.getNodeById(draggedId);

    if (node === null || node.id === target.id) {
        return false;
    }

    let parentId = target.parent_id;

    while (parentId) {
        if (parentId === draggedId) {
            return false;
        }

        parentId = vaultTreeStore.getNodeById(parentId)?.parent_id ?? null;
    }

    return true;
}

function onDragStart(event: DragEvent, nodeId: number): void {
    if (!event.dataTransfer) {
        return;
    }

    draggingNodeId.value = nodeId;

    const node = vaultTreeStore.getNodeById(nodeId);

    if (node !== null && node.is_file) {
        event.dataTransfer.setData(
            'application/vault-file',
            JSON.stringify({ type: node.type, name: node.name, url: encodeURI(node.full_path) })
        );
    }
}

function onDragEnd(): void {
    draggingNodeId.value = null;
}

function onDragOverNode(event: DragEvent, node: VaultNode): void {
    if (!event.dataTransfer) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();

    if (!canDrop(draggingNodeId.value!, node)) {
        event.dataTransfer.dropEffect = 'none';
        dropIndicator.value = null;

        return;
    }

    event.dataTransfer.dropEffect = 'move';
    dropIndicator.value = node.is_file
        ? { type: 'after', targetId: node.id }
        : { type: 'inside', targetId: node.id };
}

function onDragOverRoot(event: DragEvent): void {
    if (event.target !== event.currentTarget) {
        return;
    }

    dropIndicator.value = { type: 'root', targetId: 0 };
}

function onDrop(event: DragEvent): void {
    event.preventDefault();
    event.stopPropagation();

    const node = draggingNodeId.value ? vaultTreeStore.getNodeById(draggingNodeId.value) : null;
    const targetId = dropIndicator.value!.targetId;
    const target = vaultTreeStore.getNodeById(targetId);
    const parentId = target && (target.is_file ? target.parent_id : target.id);

    dropIndicator.value = null;

    if (!node) {
        openModal(VaultFilesImportModal, {
            vaultId: props.vaultId,
            parentId,
            dropEvent: event,
        });

        return;
    }

    vaultActions.moveNode(node.id, parentId);
}

provide('vaultTreeDragAndDrop', {
    draggingNodeId,
    dropIndicator,
    onDragStart,
    onDragEnd,
    onDragOverNode,
    onDrop,
});
</script>

<template>
    <div class="relative flex h-full w-full flex-col text-sm">
        <div v-if="layoutStore.isTreeViewLoading" class="absolute inset-0 z-30"></div>
        <div class="flex shrink-0 flex-col gap-2 p-4">
            <div class="flex w-full items-center justify-between gap-2">
                <div
                    class="flex-grow truncate pl-1 text-lg font-semibold"
                    :title="vaultStore.name ?? ''"
                >
                    {{ vaultStore.name }}
                </div>

                <div class="flex items-center">
                    <Spinner
                        v-if="layoutStore.isTreeViewLoading"
                        class="h-4 w-4 animate-spin opacity-70"
                    />
                    <Menu v-else type="dropdown">
                        <template #trigger>
                            <Bars3 class="h-5 w-5" />
                        </template>

                        <template #default="{ closeMenu }">
                            <div class="min-w-[10rem]">
                                <MenuItem
                                    label="New note"
                                    :icon="DocumentPlus"
                                    @click="
                                        closeMenu();
                                        openModal(VaultNodeCreateModal, {
                                            title: 'New note',
                                            vaultId: vaultId,
                                            parentId: null,
                                            isFile: true,
                                        });
                                    "
                                />
                                <MenuItem
                                    label="New folder"
                                    :icon="FolderPlus"
                                    @click="
                                        closeMenu();
                                        openModal(VaultNodeCreateModal, {
                                            title: 'New folder',
                                            vaultId: vaultId,
                                            parentId: null,
                                            isFile: false,
                                        });
                                    "
                                />
                                <MenuItem
                                    label="Import files"
                                    :icon="ArrowUpTray"
                                    @click="
                                        closeMenu();
                                        openModal(VaultFilesImportModal, {
                                            title: 'Import files',
                                            vaultId: vaultId,
                                            parentId: null,
                                        });
                                    "
                                />
                                <MenuItem
                                    label="Edit vault"
                                    :icon="PencilSquare"
                                    @click="
                                        closeMenu();
                                        openModal(VaultEditModal, {
                                            title: 'Edit vault',
                                            id: vaultId,
                                            name: vaultStore.name,
                                            onSuccess: (data: VaultUpdated) => {
                                                vaultStore.updateVault(data);
                                            },
                                        });
                                    "
                                />
                                <MenuItem
                                    v-if="userId === vaultStore.user?.id"
                                    label="Collaboration"
                                    :icon="UserGroup"
                                    @click="
                                        closeMenu();
                                        openModal(VaultCollaborationModal, {
                                            title: 'Collaboration',
                                            top: true,
                                            vaultId: vaultId,
                                        });
                                    "
                                />
                            </div>
                        </template>
                    </Menu>
                </div>
            </div>
        </div>

        <div id="vault-tree-scroll-container" class="mb-4 min-h-0 flex-1 overflow-y-auto">
            <div class="h-full px-4" @dragover.prevent="onDragOverRoot" @drop="onDrop">
                <VaultTreeNode v-for="id in children" :key="id" :node-id="id" :depth="0" />

                <div
                    v-if="isValidDropAfter"
                    class="border-light-base-400 dark:border-base-700 -mt-0.5 border"
                />
            </div>
        </div>
    </div>
</template>
