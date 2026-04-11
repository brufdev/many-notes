// extension-custom-table-cell.ts
import { TableCell } from '@tiptap/extension-table';

type Align = 'left' | 'center' | 'right' | null;

export const CustomTableCell = TableCell.extend({
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
