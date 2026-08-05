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
    vault_id: number;
    parent_id: number | null;
    is_file: boolean;
    type: 'audio' | 'folder' | 'image' | 'note' | 'pdf' | 'video';
    name: string;
    extension: string | null;
    full_path: string;
    url: string;
    content: string | null;
    share_url: string | null;
    updated_at: string;
}

export type VaultNodeTreeItem = Pick<
    VaultNode,
    | 'id'
    | 'vault_id'
    | 'parent_id'
    | 'is_file'
    | 'type'
    | 'name'
    | 'extension'
    | 'full_path'
    | 'url'
>;

export type VaultNodeTreeDropIndicator = {
    type: 'inside' | 'after' | 'root';
    targetId: number;
} | null;

export type VaultLink = Pick<VaultNode, 'id' | 'type' | 'name' | 'full_path'> & {
    total: number;
};

export type VaultTag = Pick<VaultNode, 'id' | 'name'> & {
    total: number;
};

export type VaultOpenedFile = VaultOpenedFileData & VaultOpenedFileTreeData;

export interface VaultOpenedFileData {
    file: VaultNode;
    links: VaultLink[];
    backlinks: VaultLink[];
    tags: VaultTag[];
}

export interface VaultOpenedFileTreeData {
    ancestors: VaultNodeTreeItem[];
    ancestorsChildren: Record<number, VaultNodeTreeItem[]>;
    siblings: VaultNodeTreeItem[];
    children: VaultNodeTreeItem[];
}

export type VaultSearchFile = Pick<
    VaultNode,
    'id' | 'type' | 'name' | 'extension' | 'full_path' | 'content' | 'updated_at'
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
