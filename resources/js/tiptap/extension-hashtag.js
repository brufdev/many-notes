import { Mark, mergeAttributes } from '@tiptap/core';

export const Hashtag = Mark.create({
    name: 'hashtag',
    inline: true,
    group: 'inline',
    selectable: false,
    inclusive: true,
    addAttributes() {
        return {
            escaped: {
                default: false,
                parseHTML: el => el.getAttribute('data-escaped') === 'true',
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
    renderHTML({ node, HTMLAttributes }) {
        return ['span', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
    },
});
