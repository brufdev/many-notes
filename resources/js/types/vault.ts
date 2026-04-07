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
};

export interface RecentVaultFile {
    id: number;
    name: string;
    full_path: string;
    time_elapsed: string;
}

export interface VaultNode {
    id: number;
    parent_id: number | null;
    is_file: boolean;
    type: 'audio' | 'folder' | 'image' | 'note' | 'pdf' | 'video';
    name: string;
    extension: string | null;
    full_path: string;
    content: string | null;
    updated_at: string;
}

export type VaultNodeTreeItem = Pick<VaultNode, 'id' | 'parent_id' | 'type' | 'name' | 'extension'>;

export type VaultTag = Pick<VaultNode, 'id' | 'name'> & {
    total: number;
};

export type VaultSearchFile = Pick<
    VaultNode,
    'id' | 'type' | 'name' | 'extension' | 'content' | 'updated_at'
>;

export type VaultEditorSearchFile = Pick<
    VaultNode,
    'id' | 'type' | 'name' | 'extension' | 'updated_at'
> & {
    dir_name: string;
    full_path: string;
    full_path_encoded: string;
};

export type VaultEditorTemplateFile = Pick<
    VaultNode,
    'id' | 'type' | 'name' | 'extension' | 'updated_at'
> & {
    full_path: string;
};
