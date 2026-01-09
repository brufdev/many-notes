import { show, templatesNode } from '@/routes/vaults';
import { children } from '@/routes/vaults/nodes';
import { useVaultStore } from '@/stores/vault';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { Vault } from '@/types/vault';
import { VaultShowPageProps } from '@/types/vault.pages';
import { router, usePage } from '@inertiajs/vue3';
import { AxiosError, AxiosResponse } from 'axios';
import { useToast } from './useToast';

const page = usePage<VaultShowPageProps>();
const { createToast } = useToast();

export function useVaultTreeActions() {
    const vaultStore = useVaultStore();
    const vaultTreeStore = useVaultTreeStore();

    function openFile(fileId: number): void {
        router.visit(show.url({ vault: vaultTreeStore.getActiveVaultId() }), {
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
        const url = templatesNode.url({ vault: vaultTreeStore.getActiveVaultId() });

        if (vaultTreeStore.isFolderLoading(nodeId)) {
            return;
        }

        vaultTreeStore.startLoadingFolder(nodeId);

        axios({
            url: url,
            method: 'patch',
            data: {
                templates_node_id: nodeId,
            },
        })
            .then((response: AxiosResponse<{ vault: Vault }>) => {
                createToast('Template folder updated', 'success');
                vaultStore.setVault(response.data.vault);
            })
            .catch((error: AxiosError) => {
                createToast(error.response?.statusText ?? 'Something went wrong', 'error');
            })
            .finally(() => {
                vaultTreeStore.finishLoadingFolder(nodeId);
            });
    }

    function handleNodesDeleted(nodeIds: number[]): void {
        vaultTreeStore.handleNodesDeleted(nodeIds);

        const selectedFileId = vaultTreeStore.getSelectedFileId();

        if (selectedFileId !== null && nodeIds.includes(selectedFileId)) {
            router.replace({ url: show.url({ vault: vaultTreeStore.getActiveVaultId() }) });
        }
    }

    return {
        openFile,
        fetchChildren,
        setTemplateFolder,
        handleNodesDeleted,
    };
}
