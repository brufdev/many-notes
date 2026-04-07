import { VaultTag } from '@/types/vault';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useVaultTagStore = defineStore('vaultTag', () => {
    const tags = ref<VaultTag[]>([]);

    function setTags(tagsList: VaultTag[]) {
        tags.value = tagsList;
    }

    function upsertTag(tag: VaultTag) {
        const index = tags.value.findIndex(t => t.id === tag.id);

        if (index === -1) {
            tags.value.unshift(tag);
        } else {
            tags.value[index] = { ...tags.value[index], ...tag };
        }
    }

    return {
        tags,
        setTags,
        upsertTag,
    };
});
