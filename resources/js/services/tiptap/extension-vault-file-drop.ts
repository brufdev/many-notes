import { Extension } from '@tiptap/core';
import { Fragment } from '@tiptap/pm/model';
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

                        const { schema, tr } = view.state;
                        const linkMark = schema.marks.link?.create({ href: url });

                        if (!linkMark) {
                            return true;
                        }

                        const resolved = view.state.doc.resolve(position.pos);
                        const prefix =
                            resolved.nodeBefore?.isText && !resolved.nodeBefore?.text?.endsWith(' ')
                                ? [schema.text(' ')]
                                : [];
                        const suffix =
                            resolved.nodeAfter?.isText && !resolved.nodeAfter?.text?.startsWith(' ')
                                ? [schema.text(' ')]
                                : [];

                        tr.insert(
                            position.pos,
                            Fragment.fromArray([
                                ...prefix,
                                schema.text(name, [linkMark]),
                                ...suffix,
                            ])
                        );
                        view.dispatch(tr);

                        return true;
                    },
                },
            }),
        ];
    },
});
