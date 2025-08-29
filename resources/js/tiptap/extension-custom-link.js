import { mergeAttributes } from '@tiptap/core';
import Link from '@tiptap/extension-link';

export const CustomLink = Link.extend({
    renderHTML({ HTMLAttributes }) {
        const { href } = HTMLAttributes;

        if (href && !href.startsWith('http')) {
            HTMLAttributes.target = '_self';
            HTMLAttributes['wire:click.prevent'] = `openFilePath('${href}')`;
            HTMLAttributes['data-href'] = href;
            delete HTMLAttributes.href;
        }

        return ['a', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
    },
});
