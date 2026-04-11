import { mergeAttributes } from '@tiptap/core';
import Image, { ImageOptions } from '@tiptap/extension-image';

export interface CustomImageOptions extends ImageOptions {
    vaultId: string | null;
}

export const CustomImage = Image.extend<CustomImageOptions>({
    addOptions() {
        return {
            ...this.parent!(),
            vaultId: null,
        };
    },

    renderHTML({ HTMLAttributes }) {
        const { src, ...rest } = HTMLAttributes;
        const resolvedSrc =
            src && !src.startsWith('http://') && !src.startsWith('https://') && this.options.vaultId
                ? `/files/${this.options.vaultId}?path=${src}`
                : src;

        return ['img', mergeAttributes(this.options.HTMLAttributes, { ...rest, src: resolvedSrc })];
    },
});
