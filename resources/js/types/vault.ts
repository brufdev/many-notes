import { User } from '.';

export interface Vault {
    id: number;
    name: string;
    templates_node_id: number | null;
    user: VaultUser;
    collaborators: VaultCollaborator[];
    created_by: number;
    updated_at: string;
}

export type VaultUser = Pick<User, 'id' | 'name' | 'email'>;

export interface VaultCollaborator extends VaultUser {
    accepted: boolean;
}

export type VaultListItem = Vault & {
    accepted_collaborators_count: number;
}

export interface RecentVaultFile {
    id: number;
    name: string;
    full_path: string;
    time_elapsed: string;
}

export interface VaultNode {
    id: number;
    vault_id: number;
    parent_id: number | null;
    type: 'audio' | 'folder' | 'image' | 'note' | 'pdf' | 'video';
    name: string;
    extension: string | null;
    content: string | null;
    created_at: string;
    updated_at: string;
}

export type VaultNodeTreeItem = Pick<VaultNode, 'id' | 'parent_id' | 'type' | 'name' | 'extension'>;
