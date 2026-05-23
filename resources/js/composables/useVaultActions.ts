import { show } from '@/routes/vaults';
import { move } from '@/routes/vaults/nodes';
import { useLayoutStore } from '@/stores/layout';
import { useVaultStore } from '@/stores/vault';
import { useVaultOpenedFileStore } from '@/stores/vaultOpenedFile';
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

function resolvePaths(currentPath: string, path: string): string {
    // If path is absolute, return it
    if (path.startsWith('/')) {
        return path;
    }

    // Get the directory of currentPath (strip the filename)
    const currentDirectory = currentPath.endsWith('/')
        ? currentPath
        : currentPath.substring(0, currentPath.lastIndexOf('/') + 1);

    // Resolve the segments of the combined path
    const combined = currentDirectory + path;
    const segments = combined.split('/');
    const resolved: string[] = [];

    for (const segment of segments) {
        if (segment === '..') {
            if (resolved.length > 0) {
                resolved.pop();
            }
        } else if (segment !== '.') {
            resolved.push(segment);
        }
    }

    const resolvedPath = resolved.join('/') || '/';

    // If the resolved path starts with '/', it's still within the vault root
    if (resolvedPath.startsWith('/') || resolvedPath === '') {
        return resolvedPath || '/';
    }

    return resolvedPath;
}

export function useVaultActions() {
    const layoutStore = useLayoutStore();
    const vaultStore = useVaultStore();
    const vaultRecentFileStore = useVaultRecentFileStore();
    const vaultOpenedFileStore = useVaultOpenedFileStore();
    const vaultTreeStore = useVaultTreeStore();
    const vaultTreeActions = useVaultTreeActions();

    function openFile(fileId: number): void {
        if (!vaultStore.id) {
            return;
        }

        layoutStore.setAppLoading(true);

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
            onFinish: () => {
                layoutStore.setAppLoading(false);
            },
        });
    }

    function openFilePath(path: string): void {
        if (!vaultStore.id || !vaultTreeStore.getSelectedFileId()) {
            return;
        }

        const recentFile = vaultRecentFileStore.recentFiles.find(
            f => f.id === vaultTreeStore.getSelectedFileId()
        );

        if (!recentFile) {
            return;
        }

        const resolvedPath = resolvePaths(recentFile.full_path, path);
        const file = vaultOpenedFileStore.links.find(l => l.full_path === resolvedPath);

        if (!file) {
            return;
        }

        openFile(file.id);
    }

    function closeFile(): void {
        if (!vaultStore.id) {
            return;
        }

        layoutStore.setAppLoading(true);

        router.visit(show.url({ vault: vaultStore.id }), {
            method: 'get',
            preserveState: true,
            only: ['openedFile'],
            onSuccess: () => {
                vaultTreeStore.setSelectedFileId(null);
            },
            onFinish: () => {
                layoutStore.setAppLoading(false);
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

        const url = move.url({
            vault: page.props.vault.id,
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

                vaultTreeActions.handleNodeUpdated(response.data.data);
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
        openFilePath,
        closeFile,
        moveNode,
    };
}
