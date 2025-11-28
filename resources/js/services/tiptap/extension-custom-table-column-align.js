import { Extension } from '@tiptap/core';

export const CustomTableColumnAlign = Extension.create({
    addCommands() {
        return {
            setTableColumnAlignment: (alignment) => ({ tr, state, dispatch }) => {
                const { $from } = state.selection;

                // Find the current cell and column index
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

                // Iterate through each row to update the column
                tableNode.descendants((node, pos) => {
                    if (node.type.name !== 'tableRow') {
                        return;
                    }

                    // Check if this row has enough columns
                    if (columnIndex < node.childCount) {
                        const targetCell = node.child(columnIndex);

                        // Calculate the actual position of the target cell
                        let cellPos = tableStart + pos + 1;

                        // Add positions of previous cells in this row
                        for (let i = 0; i < columnIndex; i++) {
                            cellPos += node.child(i).nodeSize;
                        }

                        // Update the cell with new alignment
                        const newAttrs = { ...targetCell.attrs, align: alignment };
                        tr.setNodeMarkup(cellPos, null, newAttrs);
                        modified = true;
                    }

                    // Don't descend into nested structures
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
