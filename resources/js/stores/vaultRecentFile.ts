import { VaultNode } from '@/types/vault';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useVaultRecentFileStore = defineStore('vaultRecentFile', () => {
    const recentFiles = ref<VaultNode[]>([]);

    function setRecentFiles(recentFilesList: VaultNode[]) {
        recentFiles.value = recentFilesList;
    }

    function upsertRecentFile(file: VaultNode) {
        const index = recentFiles.value.findIndex(f => f.id === file.id);

        if (index === -1) {
            recentFiles.value.unshift(file);
        } else {
            recentFiles.value[index] = { ...recentFiles.value[index], ...file };
        }

        trimAndSortRecentFiles();
    }

    function trimAndSortRecentFiles() {
        recentFiles.value.sort(
            (a, b) => new Date(b.updated_at).getTime() - new Date(a.updated_at).getTime()
        );
        recentFiles.value = recentFiles.value.slice(0, 10);
    }

    return {
        recentFiles,
        setRecentFiles,
        upsertRecentFile,
        trimAndSortRecentFiles,
    };
});
