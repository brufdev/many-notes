import TableCell from '@tiptap/extension-table-cell';

export const CustomTableCell = TableCell.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            align: {
                default: null,
                parseHTML: element => element.getAttribute('align'),
                renderHTML: attributes => {
                    if (!attributes.align) {
                        return {};
                    }

                    return {
                        align: attributes.align,
                        style: `text-align: ${attributes.align}`,
                    };
                },
            },
        };
    },
});
