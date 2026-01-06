import { VaultNodeTreeItem } from '@/types/vault';
import { defineStore } from 'pinia';
import { ref } from 'vue';

type VaultTreeState = {
    selectedFileId: number | null;
    nodesById: Record<number, VaultNodeTreeItem>;
    childrenByParentId: Record<number, number[]>;
    expandedFolderIds: Set<number>;
    loadedFolderIds: Set<number>;
    loadingNodeIds: Set<number>;
};

function createVaultTreeState(): VaultTreeState {
    return {
        selectedFileId: null,
        nodesById: {},
        childrenByParentId: {},
        expandedFolderIds: new Set(),
        loadedFolderIds: new Set(),
        loadingNodeIds: new Set(),
    };
}

export const useVaultTreeStore = defineStore('vaultTree', () => {
    const activeVaultId = ref<number | null>(null);

    const treesByVaultId = ref<Record<number, VaultTreeState>>({});

    function initializeVaultTree(
        vaultId: number,
        selectedFileId: number | null,
        rootNodes: VaultNodeTreeItem[],
        ancestors?: VaultNodeTreeItem[],
        ancestorsChildren?: Record<number, VaultNodeTreeItem[]>
    ): void {
        activeVaultId.value = vaultId;

        if (treesByVaultId.value[vaultId]) {
            setSelectedFileId(selectedFileId);

            return;
        }

        treesByVaultId.value[vaultId] = createVaultTreeState();

        setSelectedFileId(selectedFileId);
        setChildren(null, rootNodes);
        setAncestors(ancestors ?? []);
        setAncestorsChildren(ancestorsChildren ?? {});

        sortTree();
    }

    function getActiveVaultId(): number {
        if (activeVaultId.value === null) {
            throw new Error('No active vault');
        }

        return activeVaultId.value;
    }

    function getActiveTree(): VaultTreeState {
        const activeVaultId = getActiveVaultId();

        return treesByVaultId.value[activeVaultId];
    }

    function getSelectedFileId(): number | null {
        return getActiveTree().selectedFileId ?? null;
    }

    function getNodeById(id: number): VaultNodeTreeItem | null {
        return getActiveTree().nodesById[id] ?? null;
    }

    function getChildren(parentId: number | null): number[] {
        return getActiveTree().childrenByParentId[parentId ?? 0] || [];
    }

    function isFolderExpanded(id: number): boolean {
        return getActiveTree().expandedFolderIds.has(id) ?? false;
    }

    function isFolderLoaded(id: number): boolean {
        return getActiveTree().loadedFolderIds.has(id) ?? false;
    }

    function isFolderLoading(id: number): boolean {
        return getActiveTree().loadingNodeIds.has(id) ?? false;
    }

    function startLoadingFolder(id: number): void {
        getActiveTree().loadingNodeIds.add(id);
    }

    function finishLoadingFolder(id: number): void {
        getActiveTree().loadingNodeIds.delete(id);
    }

    function setLoadedFolder(id: number): void {
        getActiveTree().loadedFolderIds.add(id);
    }

    function setSelectedFileId(id: number | null): void {
        const tree = getActiveTree();

        tree.selectedFileId = id;
    }

    function ensureNode(node: VaultNodeTreeItem): void {
        const tree = getActiveTree();
        const key = node.parent_id ?? 0;

        tree.nodesById[node.id] = node;

        if (!tree.childrenByParentId[key]) {
            tree.childrenByParentId[key] = [];
        }

        if (!tree.childrenByParentId[key].includes(node.id)) {
            tree.childrenByParentId[key].push(node.id);
        }
    }

    function setChildren(parentId: number | null, children: VaultNodeTreeItem[]): void {
        const tree = getActiveTree();
        const key = parentId ?? 0;

        tree.childrenByParentId[key] = [];

        for (const child of children) {
            tree.nodesById[child.id] = child;
            tree.childrenByParentId[key].push(child.id);
        }

        sortChildren(key);
    }

    function setAncestors(ancestors: VaultNodeTreeItem[]): void {
        const tree = getActiveTree();

        for (const ancestor of ancestors) {
            ensureNode(ancestor);

            tree.expandedFolderIds.add(ancestor.id);
            tree.loadedFolderIds.add(ancestor.id);

            sortChildren(ancestor.parent_id ?? 0);
        }
    }

    function setAncestorsChildren(children: Record<number, VaultNodeTreeItem[]>): void {
        const tree = getActiveTree();

        for (const [parentIdStr, childList] of Object.entries(children)) {
            const parentId = Number(parentIdStr);

            setChildren(parentId, childList);
            tree.expandedFolderIds.add(parentId);
            tree.loadedFolderIds.add(parentId);
        }
    }

    function expandFolder(id: number): void {
        const tree = getActiveTree();

        if (!tree.expandedFolderIds.has(id)) {
            tree.expandedFolderIds.add(id);
        }
    }

    function collapseFolder(id: number): void {
        const tree = getActiveTree();

        tree.expandedFolderIds.delete(id);
    }

    function expandParents(id: number): void {
        const tree = getActiveTree();

        let current = tree.nodesById[id];

        while (current?.parent_id) {
            expandFolder(current.parent_id);
            current = tree.nodesById[current.parent_id];
        }
    }

    function sortTree(): void {
        const tree = getActiveTree();

        for (const parentId of Object.keys(tree.childrenByParentId)) {
            sortChildren(Number(parentId));
        }
    }

    function sortChildren(parentId: number): void {
        const tree = getActiveTree();

        tree.childrenByParentId[parentId].sort((firstId, secondId) => {
            const firstNode = tree.nodesById[firstId];
            const secondNode = tree.nodesById[secondId];

            const firstNodeIsFolder = firstNode.type === 'folder';
            const secondNodeIsFolder = secondNode.type === 'folder';

            if (firstNodeIsFolder !== secondNodeIsFolder) {
                return firstNodeIsFolder ? -1 : 1;
            }

            return firstNode.name.localeCompare(secondNode.name);
        });
    }

    function handleFileOpened(
        fileId: number,
        ancestors: VaultNodeTreeItem[],
        ancestorsChildren: Record<number, VaultNodeTreeItem[]>
    ): void {
        setSelectedFileId(fileId);
        setAncestors(ancestors);
        setAncestorsChildren(ancestorsChildren);
    }

    function handleNodeCreated(node: VaultNodeTreeItem): void {
        const tree = getActiveTree();

        if (node.parent_id !== null && !tree.loadedFolderIds.has(node.parent_id)) {
            return;
        }

        ensureNode(node);

        sortChildren(node.parent_id ?? 0);
    }

    function handleNodeUpdated(node: VaultNodeTreeItem): void {
        const tree = getActiveTree();

        if (node.parent_id !== null && !tree.loadedFolderIds.has(node.parent_id)) {
            return;
        }

        ensureNode(node);

        sortChildren(node.parent_id ?? 0);
    }

    function handleNodesDeleted(nodeIds: number[]): void {
        if (nodeIds.length === 0) {
            return;
        }

        const tree = getActiveTree();
        const rootNodeDeleted = tree.nodesById[nodeIds[0]];

        if (!rootNodeDeleted) {
            return;
        }

        const key = rootNodeDeleted.parent_id ?? 0;
        const siblings = tree.childrenByParentId[key];

        if (siblings) {
            tree.childrenByParentId[key] = siblings.filter(id => id !== rootNodeDeleted.id);
        }

        for (const nodeId of nodeIds) {
            const node = tree.nodesById[nodeId];

            if (!node) {
                continue;
            }

            tree.expandedFolderIds.delete(nodeId);
            tree.loadedFolderIds.delete(nodeId);
            tree.loadingNodeIds.delete(nodeId);

            delete tree.childrenByParentId[nodeId];
            delete tree.nodesById[nodeId];
        }
    }

    return {
        getActiveVaultId,
        initializeVaultTree,
        getSelectedFileId,
        getNodeById,
        getChildren,
        isFolderExpanded,
        isFolderLoaded,
        isFolderLoading,
        startLoadingFolder,
        finishLoadingFolder,
        setLoadedFolder,
        setSelectedFileId,
        setChildren,
        setAncestors,
        setAncestorsChildren,
        expandFolder,
        collapseFolder,
        expandParents,
        sortChildren,
        handleFileOpened,
        handleNodeCreated,
        handleNodeUpdated,
        handleNodesDeleted,
    };
});
