import { Fragment, Node as ProseMirrorNode } from '@tiptap/pm/model';
import { EditorView } from '@tiptap/pm/view';

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
