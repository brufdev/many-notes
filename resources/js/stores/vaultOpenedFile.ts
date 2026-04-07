import { VaultLink, VaultTag } from '@/types/vault';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useVaultOpenedFileStore = defineStore('vaultOpenedFile', () => {
    const links = ref<VaultLink[]>([]);
    const backlinks = ref<VaultLink[]>([]);
    const tags = ref<VaultTag[]>([]);

    function set(linksList?: VaultLink[], backlinksList?: VaultLink[], tagsList?: VaultTag[]) {
        links.value = linksList ?? [];
        backlinks.value = backlinksList ?? [];
        tags.value = tagsList ?? [];
    }

    return {
        links,
        backlinks,
        tags,
        set,
    };
});
