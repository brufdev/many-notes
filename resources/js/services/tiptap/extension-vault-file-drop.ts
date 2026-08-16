import { insertInlineNodes } from '@/services/tiptap/utils';
import { Extension } from '@tiptap/core';
import { Plugin, PluginKey } from '@tiptap/pm/state';

interface VaultFileDragPayload {
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

                        const { name, url } = JSON.parse(raw) as VaultFileDragPayload;
                        const position = view.posAtCoords({
                            left: event.clientX,
                            top: event.clientY,
                        });

                        if (!position) {
                            return true;
                        }

                        const { schema } = view.state;
                        const linkMark = schema.marks.link?.create({ href: url });

                        if (!linkMark) {
                            return true;
                        }

                        insertInlineNodes(view, position.pos, [schema.text(name, [linkMark])]);

                        return true;
                    },
                },
            }),
        ];
    },
});
