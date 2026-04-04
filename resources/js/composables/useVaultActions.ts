import { show } from '@/routes/vaults';
import { move } from '@/routes/vaults/nodes';
import { useLayoutStore } from '@/stores/layout';
import { useVaultStore } from '@/stores/vault';
import { useVaultRecentFileStore } from '@/stores/vaultRecentFile';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { VaultNode } from '@/types/vault';
import { VaultShowPageProps } from '@/types/vault.pages';
import { router, usePage } from '@inertiajs/vue3';
import { AxiosError } from 'axios';
import { useToast } from './useToast';
import { useVaultTreeActions } from './useVaultTreeActions';

const page = usePage<VaultShowPageProps>();
const { createToast } = useToast();

export function useVaultActions() {
    const layoutStore = useLayoutStore();
    const vaultStore = useVaultStore();
    const vaultTreeStore = useVaultTreeStore();
    const vaultRecentFileStore = useVaultRecentFileStore();
    const vaultTreeActions = useVaultTreeActions();

    function openFile(fileId: number): void {
        if (!vaultStore.id) {
            return;
        }

        router.visit(show.url({ vault: vaultStore.id }), {
            method: 'get',
            data: {
                file: fileId,
            },
            preserveState: true,
            only: ['openedFile', 'ancestors', 'ancestorsChildren'],
            onSuccess: () => {
                vaultTreeStore.handleFileOpened(
                    fileId,
                    page.props.ancestors ?? [],
                    page.props.ancestorsChildren ?? {}
                );
            },
        });
    }

    function closeFile(): void {
        if (!vaultStore.id) {
            return;
        }

        router.visit(show.url({ vault: vaultStore.id }), {
            method: 'get',
            preserveState: true,
            only: ['openedFile'],
            onSuccess: () => {
                vaultTreeStore.setSelectedFileId(null);
            },
        });
    }

    function moveNode(nodeId: number, newParentId: number | null): void {
        const node = vaultTreeStore.getNodeById(nodeId);

        if (!node) {
            createToast('Something went wrong', 'error');

            return;
        }

        if (node.parent_id === newParentId) {
            return;
        }

        layoutStore.setTreeViewLoading(true);

        const oldParentId = node.parent_id;
        const url = move.url({
            vault: vaultTreeStore.getActiveVaultId(),
            node: nodeId,
        });

        axios<{ data: VaultNode }>({
            url: url,
            method: 'patch',
            data: {
                parent_id: newParentId,
            },
        })
            .then(response => {
                const message = node.is_file ? 'File moved' : 'Folder moved';
                createToast(message, 'success');

                vaultTreeActions.handleNodeMoved(nodeId, oldParentId, newParentId);
                vaultRecentFileStore.upsertRecentFile(response.data.data);
            })
            .catch((error: AxiosError) => {
                createToast(error.response?.statusText ?? 'Something went wrong', 'error');
            })
            .finally(() => {
                layoutStore.setTreeViewLoading(false);
            });
    }

    return {
        openFile,
        closeFile,
        moveNode,
    };
}
