import { getUploadableClipboardFiles } from '@/composables/useVaultFileUpload';
import { createVaultFileNode, insertInlineNodes } from '@/services/tiptap/utils';
import { VaultNode } from '@/types/vault';
import { Extension } from '@tiptap/core';
import { Node as ProseMirrorNode } from '@tiptap/pm/model';
import { Plugin, PluginKey } from '@tiptap/pm/state';
import { Decoration, DecorationSet, EditorView } from '@tiptap/pm/view';

export interface VaultFileUploadRequest {
    files: File[];
    onSuccess: (files: VaultNode[]) => void;
    onFinish: () => void;
}

export interface VaultFileUploadOptions {
    uploadFiles: ((request: VaultFileUploadRequest) => void) | null;
    placeholderClass: string;
}

interface PlaceholderMeta {
    add?: { id: symbol; position: number; label: string };
    remove?: { id: symbol };
}

const uploadKey = new PluginKey<DecorationSet>('vaultFileUpload');

function createPlaceholder(label: string, className: string): HTMLElement {
    const element = document.createElement('span');
    element.className = className;
    element.textContent = label;

    return element;
}

function findPlaceholder(view: EditorView, id: symbol): number | null {
    const decorations = uploadKey.getState(view.state);
    const found = decorations?.find(undefined, undefined, spec => spec.id === id);

    return found?.length ? found[0].from : null;
}

function buildLabel(files: File[]): string {
    return files.length === 1 ? `Uploading ${files[0].name}…` : `Uploading ${files.length} files…`;
}

function buildNodes(view: EditorView, files: VaultNode[]): ProseMirrorNode[] {
    const { schema } = view.state;

    return files.map(file =>
        createVaultFileNode(schema, file.type, file.name, encodeURI(file.full_path))
    );
}

export const VaultFileUpload = Extension.create<VaultFileUploadOptions>({
    name: 'vaultFileUpload',

    addOptions() {
        return {
            uploadFiles: null,
            placeholderClass: '',
        };
    },

    addProseMirrorPlugins() {
        const placeholderClass = this.options.placeholderClass;

        const startUpload = (view: EditorView, files: File[], position: number) => {
            const uploadFiles = this.options.uploadFiles;

            if (!uploadFiles) {
                return;
            }

            const id = Symbol('vaultFileUpload');
            const addMeta: PlaceholderMeta = {
                add: { id, position, label: buildLabel(files) },
            };
            view.dispatch(view.state.tr.setMeta(uploadKey, addMeta));

            uploadFiles({
                files,
                onSuccess: uploadedFiles => {
                    if (view.isDestroyed) {
                        return;
                    }

                    const placeholderPosition = findPlaceholder(view, id);

                    if (placeholderPosition === null) {
                        return;
                    }

                    insertInlineNodes(view, placeholderPosition, buildNodes(view, uploadedFiles));
                },
                onFinish: () => {
                    if (view.isDestroyed) {
                        return;
                    }

                    const removeMeta: PlaceholderMeta = { remove: { id } };
                    view.dispatch(view.state.tr.setMeta(uploadKey, removeMeta));
                },
            });
        };

        return [
            new Plugin({
                key: uploadKey,
                state: {
                    init: () => DecorationSet.empty,
                    apply(tr, set) {
                        set = set.map(tr.mapping, tr.doc);

                        const meta = tr.getMeta(uploadKey) as PlaceholderMeta | undefined;

                        if (meta?.add) {
                            set = set.add(tr.doc, [
                                Decoration.widget(
                                    meta.add.position,
                                    createPlaceholder(meta.add.label, placeholderClass),
                                    { id: meta.add.id, side: 1 }
                                ),
                            ]);
                        }

                        if (meta?.remove) {
                            const id = meta.remove.id;
                            set = set.remove(
                                set.find(undefined, undefined, spec => spec.id === id)
                            );
                        }

                        return set;
                    },
                },
                props: {
                    decorations(state) {
                        return uploadKey.getState(state);
                    },

                    handlePaste(view, event) {
                        if (!view.editable) {
                            return false;
                        }

                        const files = getUploadableClipboardFiles(event);

                        if (files.length === 0) {
                            return false;
                        }

                        event.preventDefault();
                        startUpload(view, files, view.state.selection.from);

                        return true;
                    },

                    handleDrop(view, event) {
                        if (!view.editable) {
                            return false;
                        }

                        const files = Array.from(event.dataTransfer?.files ?? []);

                        if (files.length === 0) {
                            return false;
                        }

                        event.preventDefault();

                        const coordinates = view.posAtCoords({
                            left: event.clientX,
                            top: event.clientY,
                        });
                        startUpload(view, files, coordinates?.pos ?? view.state.selection.from);

                        return true;
                    },
                },
            }),
        ];
    },
});
