import { show, update } from '@/routes/vaults';
import { children } from '@/routes/vaults/nodes';
import { useLayoutStore } from '@/stores/layout';
import { useVaultStore } from '@/stores/vault';
import { useVaultTreeStore } from '@/stores/vaultTree';
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

    function fetchChildren(
        parentId: number | null,
        onSuccess?: (response: AxiosResponse) => void,
        onError?: (error: AxiosError) => void
    ): void {
        const key = parentId ?? 0;
        const url = children.url({ vault: vaultTreeStore.getActiveVaultId(), node: key });

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
        const url = update.url({ vault: vaultTreeStore.getActiveVaultId() });

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

    function handleNodesDeleted(nodeIds: number[], showToast = true): void {
        vaultTreeStore.handleNodesDeleted(nodeIds);

        const selectedFileId = vaultTreeStore.getSelectedFileId();

        if (selectedFileId !== null && nodeIds.includes(selectedFileId)) {
            router.visit(show.url({ vault: vaultTreeStore.getActiveVaultId() }), {
                replace: true,
                fresh: true,
                onSuccess: () => {
                    if (showToast) {
                        createToast('File deleted', 'warning');
                    }
                },
            });
        }
    }

    function handleNodeMoved(
        nodeId: number,
        oldParentId: number | null,
        newParentId: number | null
    ): void {
        const selectedFileId = vaultTreeStore.getSelectedFileId();

        vaultTreeStore.handleNodeMoved(nodeId, oldParentId, newParentId);

        if (
            selectedFileId !== null &&
            newParentId !== null &&
            vaultTreeStore.isNodeInSubtree(selectedFileId, nodeId) &&
            !vaultTreeStore.isFolderLoaded(newParentId)
        ) {
            router.reload({
                only: ['ancestors', 'ancestorsChildren'],
                onSuccess: () => {
                    vaultTreeStore.setAncestors(page.props.ancestors ?? []);
                    vaultTreeStore.setAncestorsChildren(page.props.ancestorsChildren ?? {});
                },
            });
        }
    }

    return {
        fetchChildren,
        setTemplateFolder,
        handleNodesDeleted,
        handleNodeMoved,
    };
}
