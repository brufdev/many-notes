import { mergeAttributes } from '@tiptap/core';
import Image, { ImageOptions } from '@tiptap/extension-image';

export interface CustomImageOptions extends ImageOptions {
    vaultId: string | null;
    baseUrl: string | null;
}

export const CustomImage = Image.extend<CustomImageOptions>({
    addOptions() {
        return {
            ...this.parent!(),
            vaultId: null,
            baseUrl: null,
        };
    },

    renderHTML({ HTMLAttributes }) {
        const { src, ...rest } = HTMLAttributes;
        const baseUrl =
            this.options.baseUrl ??
            (this.options.vaultId ? `/files/${this.options.vaultId}` : null);
        const resolvedSrc =
            src && !src.startsWith('http://') && !src.startsWith('https://') && baseUrl
                ? `${baseUrl}?path=${src}`
                : src;

        return ['img', mergeAttributes(this.options.HTMLAttributes, { ...rest, src: resolvedSrc })];
    },
});
