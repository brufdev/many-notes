import { mergeAttributes } from '@tiptap/core';
import Image from '@tiptap/extension-image';

export const CustomImage = Image.extend({
    renderHTML({ HTMLAttributes }) {
        const { src } = HTMLAttributes;

        if (src && !src.startsWith('http://') && !src.startsWith('https://') && this.options.vaultId) {
            HTMLAttributes.src = `/files/${this.options.vaultId}?path=${src}`;
        }

        return ['img', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes)];
    },
});
