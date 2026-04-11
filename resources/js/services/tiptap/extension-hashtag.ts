import { Mark, mergeAttributes } from '@tiptap/core';

export const Hashtag = Mark.create({
    name: 'hashtag',
    inclusive: true,

    addAttributes() {
        return {
            escaped: {
                default: false,
                parseHTML: element => element.dataset.escaped === 'true',
                renderHTML: attrs => ({
                    'data-hashtag': 'true',
                    'data-escaped': attrs.escaped ? 'true' : 'false',
                }),
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'span[data-hashtag]',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['span', mergeAttributes(HTMLAttributes), 0];
    },
});
