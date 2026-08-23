import { createVaultFileNode, insertInlineNodes } from '@/services/tiptap/utils';
import { VaultNode } from '@/types/vault';
import { Extension } from '@tiptap/core';
import { Plugin, PluginKey } from '@tiptap/pm/state';

interface VaultFileDragPayload {
    type: VaultNode['type'];
    name: string;
    url: string;
}

export const VaultFileDrop = Extension.create({
    name: 'vaultFileDrop',

    addProseMirrorPlugins() {
        return [
            new Plugin({
                key: new PluginKey('vaultFileDrop'),
                props: {
                    handleDrop(view, event) {
                        const raw = event.dataTransfer?.getData('application/vault-file');

                        if (!raw) {
                            return false;
                        }

                        event.preventDefault();

                        const { type, name, url } = JSON.parse(raw) as VaultFileDragPayload;
                        const position = view.posAtCoords({
                            left: event.clientX,
                            top: event.clientY,
                        });

                        if (!position) {
                            return true;
                        }

                        insertInlineNodes(view, position.pos, [
                            createVaultFileNode(view.state.schema, type, name, url),
                        ]);

                        return true;
                    },
                },
            }),
        ];
    },
});
