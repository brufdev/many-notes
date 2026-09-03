import { update } from '@/routes/vaults';
import { useLayoutStore } from '@/stores/layout';
import { useVaultStore } from '@/stores/vault';
import { useVaultTemplateStore } from '@/stores/vaultTemplate';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { VaultNode } from '@/types/vault';
import { VaultUpdated } from '@/types/vault.events';
import { VaultShowPageProps } from '@/types/vault.pages';
import { router, usePage } from '@inertiajs/vue3';
import { useRequest } from './useRequest';
import { useToast } from './useToast';

export function useVaultTreeActions() {
    const page = usePage<VaultShowPageProps>();
    const { createToast } = useToast();
    const layoutStore = useLayoutStore();
    const vaultStore = useVaultStore();
    const vaultTemplateStore = useVaultTemplateStore();
    const vaultTreeStore = useVaultTreeStore();
    const childrenRequest = useRequest({});
    const templateFolderRequest = useRequest<{ templates_node_id: number }>({
        templates_node_id: 0,
    });

    function toggleFolder(nodeId: number): void {
        const node = vaultTreeStore.getNodeById(nodeId);

        if (!node || node.is_file) {
            return;
        }

        if (vaultTreeStore.isFolderExpanded(nodeId)) {
            vaultTreeStore.collapseFolder(nodeId);
        } else if (vaultTreeStore.isFolderLoaded(nodeId)) {
            vaultTreeStore.expandFolder(nodeId);
        } else {
            fetchChildren(nodeId, nodes => {
                vaultTreeStore.setChildren(nodeId, nodes);
                vaultTreeStore.sortChildren(nodeId);
                vaultTreeStore.expandFolder(nodeId);
                vaultTreeStore.setLoadedFolder(nodeId);
            });
        }
    }

    function fetchChildren(
        parentId: number | null,
        onSuccess?: (nodes: VaultNode[]) => void
    ): void {
        const key = parentId ?? 0;
        const url = children.url({ vault: page.props.vault.id, node: key });

        if (vaultTreeStore.isFolderLoading(key)) {
            return;
        }

        vaultTreeStore.startLoadingFolder(key);

        childrenRequest.get<{ children?: VaultNode[] }>(url, {
            onSuccess: response => onSuccess?.(response.children ?? []),
            onFinish: () => vaultTreeStore.finishLoadingFolder(key),
        });
    }

    function setTemplateFolder(nodeId: number): void {
        const url = update.url({ vault: page.props.vault.id });

        if (vaultTreeStore.isFolderLoading(nodeId)) {
            return;
        }

        layoutStore.setTreeViewLoading(true);

        templateFolderRequest.templates_node_id = nodeId;

        templateFolderRequest.patch<{ data: VaultUpdated }>(url, {
            onSuccess: response => {
                createToast('Template folder updated', 'success');
                handleVaultUpdated(response.data);
            },
            onFinish: () => layoutStore.setTreeViewLoading(false),
        });
    }

    function handleVaultUpdated(data: VaultUpdated): void {
        const templateFolderChanged = vaultStore.templates_node_id !== data.templates_node_id;

        vaultStore.updateVault(data);

        if (templateFolderChanged) {
            router.reload({ only: ['templateNodes'] });
        }
    }

    function handleNodeUpdated(node: VaultNode): void {
        const previousNodeData = vaultTreeStore.getNodeById(node.id);
        const selectedFileId = vaultTreeStore.getSelectedFileId();

        vaultTreeStore.handleNodeSaved(node);

        if (
            selectedFileId !== null &&
            node.parent_id !== null &&
            previousNodeData !== null &&
            previousNodeData.parent_id !== node.parent_id &&
            vaultTreeStore.isNodeInSubtree(selectedFileId, node.id) &&
            !vaultTreeStore.isFolderLoaded(node.parent_id)
        ) {
            router.reload({
                only: ['openedFile'],
                onSuccess: () => {
                    vaultTreeStore.setAncestors(page.props.openedFile?.ancestors ?? []);
                    vaultTreeStore.setAncestorsChildren(
                        page.props.openedFile?.ancestorsChildren ?? {}
                    );
                },
            });
        }
    }

    function handleNodesDeleted(nodeIds: number[]): void {
        vaultTreeStore.handleNodesDeleted(nodeIds);

        for (const nodeId of nodeIds) {
            vaultTemplateStore.remove(nodeId);
        }
    }

    return {
        toggleFolder,
        fetchChildren,
        setTemplateFolder,
        handleVaultUpdated,
        handleNodeUpdated,
        handleNodesDeleted,
    };
}
