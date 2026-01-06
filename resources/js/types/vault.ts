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

export type VaultNodeTreeItem = Pick<
    VaultNode,
    'id' | 'parent_id' | 'type' | 'name' | 'extension'
>;
