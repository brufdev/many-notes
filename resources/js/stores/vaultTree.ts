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

export const useVaultTreeStore = defineStore('vaultTree', () => {
    const tree = ref<VaultTreeState>({
        selectedFileId: null,
        nodesById: {},
        childrenByParentId: {},
        expandedFolderIds: new Set(),
        loadedFolderIds: new Set(),
        loadingNodeIds: new Set(),
    });

    function initializeVaultTree(
        selectedFileId: number | null,
        rootNodes: VaultNodeTreeItem[],
        ancestors?: VaultNodeTreeItem[],
        ancestorsChildren?: Record<number, VaultNodeTreeItem[]>
    ): void {
        setSelectedFileId(selectedFileId);
        setChildren(null, rootNodes);
        setAncestors(ancestors ?? []);
        setAncestorsChildren(ancestorsChildren ?? {});
        sortTree();
    }

    function getSelectedFileId(): number | null {
        return tree.value.selectedFileId ?? null;
    }

    function getNodeById(id: number): VaultNodeTreeItem | null {
        return tree.value.nodesById[id] ?? null;
    }

    function getChildren(parentId: number | null): number[] {
        return tree.value.childrenByParentId[parentId ?? 0] || [];
    }

    function isFolderExpanded(id: number): boolean {
        return tree.value.expandedFolderIds.has(id) ?? false;
    }

    function isFolderLoaded(id: number): boolean {
        return tree.value.loadedFolderIds.has(id) ?? false;
    }

    function isFolderLoading(id: number): boolean {
        return tree.value.loadingNodeIds.has(id) ?? false;
    }

    function isNodeInSubtree(nodeId: number, rootNodeId: number): boolean {
        if (rootNodeId === nodeId) {
            return true;
        }

        if (!tree.value.loadedFolderIds.has(rootNodeId)) {
            return false;
        }

        const stack: number[] = [...(tree.value.childrenByParentId[rootNodeId] ?? [])];

        while (stack.length > 0) {
            const currentId = stack.pop()!;

            if (currentId === nodeId) {
                return true;
            }

            if (tree.value.loadedFolderIds.has(currentId)) {
                const children = tree.value.childrenByParentId[currentId];

                if (children) {
                    stack.push(...children);
                }
            }
        }

        return false;
    }

    function startLoadingFolder(id: number): void {
        tree.value.loadingNodeIds.add(id);
    }

    function finishLoadingFolder(id: number): void {
        tree.value.loadingNodeIds.delete(id);
    }

    function setLoadedFolder(id: number): void {
        tree.value.loadedFolderIds.add(id);
    }

    function setSelectedFileId(id: number | null): void {
        tree.value.selectedFileId = id;
    }

    function ensureNode(node: VaultNodeTreeItem): void {
        const key = node.parent_id ?? 0;

        tree.value.nodesById[node.id] = node;

        if (!tree.value.childrenByParentId[key]) {
            tree.value.childrenByParentId[key] = [];
        }

        if (!tree.value.childrenByParentId[key].includes(node.id)) {
            tree.value.childrenByParentId[key].push(node.id);
        }
    }

    function setChildren(parentId: number | null, children: VaultNodeTreeItem[]): void {
        const key = parentId ?? 0;

        tree.value.childrenByParentId[key] = [];

        for (const child of children) {
            tree.value.nodesById[child.id] = child;
            tree.value.childrenByParentId[key].push(child.id);
        }

        sortChildren(key);
    }

    function setAncestors(ancestors: VaultNodeTreeItem[]): void {
        for (const ancestor of ancestors) {
            ensureNode(ancestor);
            tree.value.expandedFolderIds.add(ancestor.id);
            tree.value.loadedFolderIds.add(ancestor.id);
            sortChildren(ancestor.parent_id ?? 0);
        }
    }

    function setAncestorsChildren(children: Record<number, VaultNodeTreeItem[]>): void {
        for (const [parentIdStr, childList] of Object.entries(children)) {
            const parentId = Number(parentIdStr);

            setChildren(parentId, childList);
            tree.value.expandedFolderIds.add(parentId);
            tree.value.loadedFolderIds.add(parentId);
        }
    }

    function expandFolder(id: number): void {
        if (!tree.value.expandedFolderIds.has(id)) {
            tree.value.expandedFolderIds.add(id);
        }
    }

    function collapseFolder(id: number): void {
        tree.value.expandedFolderIds.delete(id);
    }

    function expandParents(id: number): void {
        let current = tree.value.nodesById[id];

        while (current?.parent_id) {
            expandFolder(current.parent_id);
            current = tree.value.nodesById[current.parent_id];
        }
    }

    function sortTree(): void {
        for (const parentId of Object.keys(tree.value.childrenByParentId)) {
            sortChildren(Number(parentId));
        }
    }

    function sortChildren(parentId: number): void {
        tree.value.childrenByParentId[parentId].sort((firstId, secondId) => {
            const firstNode = tree.value.nodesById[firstId];
            const secondNode = tree.value.nodesById[secondId];

            if (firstNode.is_file !== secondNode.is_file) {
                return firstNode.is_file ? 1 : -1;
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

    function handleNodeSaved(node: VaultNodeTreeItem): void {
        if (tree.value.nodesById[node.id]) {
            const previousKey = tree.value.nodesById[node.id].parent_id ?? 0;

            tree.value.childrenByParentId[previousKey] = tree.value.childrenByParentId[
                previousKey
            ].filter(id => id !== node.id);
        }

        if (node.parent_id !== null && !tree.value.loadedFolderIds.has(node.parent_id)) {
            return;
        }

        ensureNode(node);
        sortChildren(node.parent_id ?? 0);
    }

    function handleNodesDeleted(nodeIds: number[]): void {
        if (nodeIds.length === 0) {
            return;
        }

        const rootNodeDeleted = tree.value.nodesById[nodeIds[0]];

        if (!rootNodeDeleted) {
            return;
        }

        const key = rootNodeDeleted.parent_id ?? 0;
        const siblings = tree.value.childrenByParentId[key];

        if (siblings) {
            tree.value.childrenByParentId[key] = siblings.filter(id => id !== rootNodeDeleted.id);
        }

        for (const nodeId of nodeIds) {
            const node = tree.value.nodesById[nodeId];

            if (!node) {
                continue;
            }

            tree.value.expandedFolderIds.delete(nodeId);
            tree.value.loadedFolderIds.delete(nodeId);
            tree.value.loadingNodeIds.delete(nodeId);

            delete tree.value.childrenByParentId[nodeId];
            delete tree.value.nodesById[nodeId];
        }
    }

    function handleNodeMoved(
        nodeId: number,
        oldParentId: number | null,
        newParentId: number | null
    ): void {
        const node = getNodeById(nodeId);

        if (!node) {
            return;
        }

        const oldParentIdValue = oldParentId ?? 0;
        const newParentIdValue = newParentId ?? 0;
        const siblings = tree.value.childrenByParentId[oldParentIdValue];

        if (siblings) {
            tree.value.childrenByParentId[oldParentIdValue] = siblings.filter(id => id !== nodeId);
        }

        node.parent_id = newParentId;

        if (newParentId === null || isFolderLoaded(newParentIdValue)) {
            ensureNode(node);
            sortChildren(newParentIdValue);
        }
    }

    return {
        initializeVaultTree,
        getSelectedFileId,
        getNodeById,
        getChildren,
        isFolderExpanded,
        isFolderLoaded,
        isFolderLoading,
        isNodeInSubtree,
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
        handleNodeSaved,
        handleNodesDeleted,
        handleNodeMoved,
    };
});
