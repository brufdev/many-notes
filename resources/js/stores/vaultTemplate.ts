import { VaultEditorTemplateFile } from '@/types/vault';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useVaultTemplateStore = defineStore('vaultTemplates', () => {
    const templates = ref<VaultEditorTemplateFile[]>([]);

    function setTemplates(templateNodes: VaultEditorTemplateFile[] | null): void {
        templates.value = templateNodes ?? [];

        sortTemplates();
    }

    function upsert(node: VaultEditorTemplateFile) {
        const index = templates.value.findIndex(f => f.id === node.id);

        if (index === -1) {
            templates.value.unshift(node);
        } else {
            templates.value[index] = { ...templates.value[index], ...node };
        }

        sortTemplates();
    }

    function remove(id: number) {
        templates.value = templates.value.filter(f => f.id !== id);
    }

    function sortTemplates() {
        templates.value.sort((a, b) => a.name.localeCompare(b.name));
    }

    return {
        templates,
        setTemplates,
        upsert,
        remove,
    };
});
