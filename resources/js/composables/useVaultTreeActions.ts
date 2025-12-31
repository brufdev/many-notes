import { show } from '@/routes/vaults';
import { useVaultTreeStore } from '@/stores/vaultTree';
import { VaultShowPageProps } from '@/types/vault.pages';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage<VaultShowPageProps>();

export function useVaultTreeActions() {
    const vaultTreeStore = useVaultTreeStore();

    function openFile(fileId: number): void {
        const url = show.url({
            vault: vaultTreeStore.getActiveVaultId(),
        });

        router.visit(url, {
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

    return {
        openFile,
    };
}
