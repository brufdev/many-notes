import { TableHeader } from '@tiptap/extension-table';

type Align = 'left' | 'center' | 'right' | null;

export const CustomTableHeader = TableHeader.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            align: {
                default: null as Align,
                parseHTML: (element: HTMLElement): Align => element.getAttribute('align') as Align,
                renderHTML: (attributes: { align: Align }) => {
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
