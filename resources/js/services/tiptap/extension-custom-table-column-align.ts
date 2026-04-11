import { CommandProps, Extension } from '@tiptap/core';
import { Node } from '@tiptap/pm/model';

type Align = 'left' | 'center' | 'right' | null;

declare module '@tiptap/core' {
    interface Commands<ReturnType> {
        tableColumnAlign: {
            setTableColumnAlignment: (alignment: Align) => ReturnType;
        };
    }
}

export const CustomTableColumnAlign = Extension.create({
    name: 'tableColumnAlign',

    addCommands() {
        return {
            setTableColumnAlignment:
                (alignment: Align) =>
                ({ tr, state, dispatch }: CommandProps) => {
                    const { $from } = state.selection;

                    let tableDepth = -1;
                    let rowDepth = -1;
                    let cellDepth = -1;

                    for (let depth = $from.depth; depth > 0; depth--) {
                        const node = $from.node(depth);

                        if (node.type.name === 'tableCell' || node.type.name === 'tableHeader') {
                            cellDepth = depth;
                        } else if (node.type.name === 'tableRow') {
                            rowDepth = depth;
                        } else if (node.type.name === 'table') {
                            tableDepth = depth;
                            break;
                        }
                    }

                    if (tableDepth === -1 || rowDepth === -1 || cellDepth === -1) {
                        return false;
                    }

                    const columnIndex = $from.index(rowDepth);
                    const tableNode = $from.node(tableDepth);
                    const tableStart = $from.start(tableDepth);
                    let modified = false;

                    tableNode.descendants((node: Node, pos: number) => {
                        if (node.type.name !== 'tableRow') {
                            return;
                        }

                        if (columnIndex < node.childCount) {
                            const targetCell = node.child(columnIndex);

                            let cellPos = tableStart + pos + 1;

                            for (let i = 0; i < columnIndex; i++) {
                                cellPos += node.child(i).nodeSize;
                            }

                            tr.setNodeMarkup(cellPos, null, {
                                ...targetCell.attrs,
                                align: alignment,
                            });
                            modified = true;
                        }

                        return false;
                    });

                    if (modified && dispatch) {
                        dispatch(tr);
                    }

                    return modified;
                },
        };
    },
});
