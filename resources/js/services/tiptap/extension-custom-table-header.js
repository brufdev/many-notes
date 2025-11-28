import TableHeader from '@tiptap/extension-table-header';

export const CustomTableHeader = TableHeader.extend({
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
