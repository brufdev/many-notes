import { update } from '@/routes/vaults';
import { children } from '@/routes/vaults/nodes';
import { useLayoutStore } from '@/stores/layout';
import { useVaultStore } from '@/stores/vault';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { VaultNode } from '@/types/vault';
import { VaultShowPageProps } from '@/types/vault.pages';
import { router, usePage } from '@inertiajs/vue3';
import { AxiosError, AxiosResponse } from 'axios';
import { useToast } from './useToast';

const page = usePage<VaultShowPageProps>();
const { createToast } = useToast();

export function useVaultTreeActions() {
    const layoutStore = useLayoutStore();
    const vaultStore = useVaultStore();
    const vaultTreeStore = useVaultTreeStore();

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
            fetchChildren(
                nodeId,
                response => {
                    vaultTreeStore.setChildren(nodeId, response.data.children ?? []);
                    vaultTreeStore.sortChildren(nodeId);
                    vaultTreeStore.expandFolder(nodeId);
                    vaultTreeStore.setLoadedFolder(nodeId);
                },
                error => {
                    createToast(error.response?.statusText ?? 'Something went wrong', 'error');
                }
            );
        }
    }

    function fetchChildren(
        parentId: number | null,
        onSuccess?: (response: AxiosResponse) => void,
        onError?: (error: AxiosError) => void
    ): void {
        const key = parentId ?? 0;
        const url = children.url({ vault: page.props.vault.id, node: key });

        if (vaultTreeStore.isFolderLoading(key)) {
            return;
        }

        vaultTreeStore.startLoadingFolder(key);

        axios({
            url: url,
            method: 'get',
        })
            .then((response: AxiosResponse) => {
                onSuccess?.(response);
            })
            .catch((error: AxiosError) => {
                onError?.(error);
            })
            .finally(() => {
                vaultTreeStore.finishLoadingFolder(key);
            });
    }

    function setTemplateFolder(nodeId: number): void {
        const url = update.url({ vault: page.props.vault.id });

        if (vaultTreeStore.isFolderLoading(nodeId)) {
            return;
        }

        layoutStore.setTreeViewLoading(true);

        axios({
            url: url,
            method: 'patch',
            data: {
                templates_node_id: nodeId,
            },
        })
            .then((response: AxiosResponse) => {
                createToast('Template folder updated', 'success');
                vaultStore.updateVault(response.data.data);
            })
            .catch((error: AxiosError) => {
                createToast(error.response?.statusText ?? 'Something went wrong', 'error');
            })
            .finally(() => {
                layoutStore.setTreeViewLoading(false);
            });
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
    }

    return {
        toggleFolder,
        fetchChildren,
        setTemplateFolder,
        handleNodeUpdated,
        handleNodesDeleted,
    };
}
