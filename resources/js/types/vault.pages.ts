import {
    Vault,
    VaultEditorTemplateFile,
    VaultNode,
    VaultNodeTreeItem,
    VaultOpenedFile,
    VaultTag,
} from './vault';

export type VaultShowPageProps = {
    vault: Vault;
    recentFiles: VaultNode[];
    templateNodes: VaultEditorTemplateFile[] | null;
    rootNodes: VaultNodeTreeItem[];
    openedFile?: VaultOpenedFile;
    tags: VaultTag[];
};
