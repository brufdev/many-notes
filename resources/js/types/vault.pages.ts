import { AppPageProps } from '.';
import {
    Vault,
    VaultEditorTemplateFile,
    VaultNode,
    VaultNodeTreeItem,
    VaultOpenedFile,
    VaultTag,
} from './vault';

export interface VaultShowPageProps extends AppPageProps {
    vault: Vault;
    recentFiles: VaultNode[];
    templateNodes: VaultEditorTemplateFile[] | null;
    rootNodes: VaultNodeTreeItem[];
    openedFile?: VaultOpenedFile;
    tags: VaultTag[];
}
