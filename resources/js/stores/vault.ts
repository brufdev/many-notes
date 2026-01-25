import { Vault, VaultCollaborator, VaultUser } from '@/types/vault';
import { VaultUpdated } from '@/types/vault.events';
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useVaultStore = defineStore('vault', () => {
    const name = ref<string | null>(null);
    const templates_node_id = ref<number | null>(null);
    const user = ref<VaultUser | null>(null);
    const collaborators = ref<VaultCollaborator[]>([]);

    function setVault(vault: Vault | null): void {
        name.value = vault?.name ?? null;
        templates_node_id.value = vault?.templates_node_id ?? null;
        user.value = vault?.user ?? null;
        collaborators.value = vault?.collaborators ?? [];
    }

    function updateVault(payload: VaultUpdated): void {
        name.value = payload.vault.name;
        templates_node_id.value = payload.vault.templates_node_id;
    }

    function addCollaborator(collaborator: VaultCollaborator): void {
        collaborators.value.push(collaborator);
    }

    function removeCollaborator(userId: number): void {
        collaborators.value = collaborators.value.filter(
            collaborator => collaborator.id !== userId
        );
    }

    return {
        name,
        templates_node_id,
        user,
        collaborators,
        setVault,
        updateVault,
        addCollaborator,
        removeCollaborator,
    };
});
