import { VaultNode } from '@/types/vault';
import { Fragment, Node as ProseMirrorNode, Schema } from '@tiptap/pm/model';
import { EditorView } from '@tiptap/pm/view';

export function createVaultFileNode(
    schema: Schema,
    type: VaultNode['type'],
    name: string,
    path: string
): ProseMirrorNode {
    if (type === 'image' && schema.nodes.image) {
        return schema.nodes.image.create({ src: path, alt: name, title: null });
    }

    const linkMark = schema.marks.link?.create({ href: path });

    return schema.text(name, linkMark ? [linkMark] : []);
}

export function insertInlineNodes(
    view: EditorView,
    position: number,
    nodes: ProseMirrorNode[]
): void {
    if (nodes.length === 0) {
        return;
    }

    const { schema, tr } = view.state;
    const resolved = view.state.doc.resolve(position);
    const nodeBefore = resolved.nodeBefore;
    const nodeAfter = resolved.nodeAfter;
    const prefix =
        nodeBefore?.isInline && !(nodeBefore.isText && nodeBefore.text?.endsWith(' '))
            ? [schema.text(' ')]
            : [];
    const suffix =
        nodeAfter?.isInline && !(nodeAfter.isText && nodeAfter.text?.startsWith(' '))
            ? [schema.text(' ')]
            : [];
    const separated = nodes.flatMap((node, index) =>
        index === 0 ? [node] : [schema.text(' '), node]
    );

    tr.insert(position, Fragment.fromArray([...prefix, ...separated, ...suffix]));
    view.dispatch(tr);
}
