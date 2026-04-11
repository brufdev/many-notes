import { InputRule, mergeAttributes } from '@tiptap/core';
import Link from '@tiptap/extension-link';
import type { MarkType } from '@tiptap/pm/model';
import { Plugin, PluginKey } from '@tiptap/pm/state';

export interface CustomLinkOptions extends Record<string, unknown> {
    onOpenFile?: (href: string) => void;
}

const angleBracketLinkRule = (type: MarkType): InputRule => {
    return new InputRule({
        find: /<([^>\s]+)>$/,
        handler: ({ range, match, commands }) => {
            const value = match[1];
            const isEmail = /^[^>\s]+@[^>\s]+\.[^>\s]+$/.test(value);
            const isUrl = /^https?:\/\/[^>]+$/.test(value);

            if (!isEmail && !isUrl) {
                return;
            }

            const href = isEmail ? `mailto:${value}` : value;

            // Replace the full match <url>
            commands.insertContentAt(range, {
                type: 'text',
                text: value,
                marks: [
                    {
                        type: type.name,
                        attrs: {
                            href,
                            'data-angle-bracket': 'true',
                        },
                    },
                ],
            });
        },
    });
};

export const CustomLink = Link.extend<CustomLinkOptions>({
    addOptions() {
        return {
            ...this.parent?.(),
            onOpenFile: undefined,
        };
    },

    addAttributes() {
        return {
            ...this.parent?.(),
            'data-angle-bracket': {
                default: null,
                parseHTML: (element: HTMLElement) => element.dataset.angleBracket ?? null,
                renderHTML: (attributes: Record<string, unknown>) => {
                    if (!attributes['data-angle-bracket']) {
                        return {};
                    }

                    return {
                        'data-angle-bracket': attributes['data-angle-bracket'],
                    };
                },
            },
            title: {
                default: null,
                parseHTML: (element: HTMLElement) => element.getAttribute('title'),
                renderHTML: (attributes: Record<string, unknown>) => {
                    if (!attributes['title']) {
                        return {};
                    }

                    return {
                        title: attributes['title'],
                    };
                },
            },
        };
    },

    addPasteRules() {
        return [];
    },

    addInputRules() {
        return [angleBracketLinkRule(this.type)];
    },

    renderHTML({ HTMLAttributes }: { HTMLAttributes: Record<string, unknown> }) {
        const href = HTMLAttributes['href'] as string | undefined;

        if (href) {
            if (/^[^>]+@[^>]+\.[^>]+$/.test(href)) {
                // Email links
                HTMLAttributes['target'] = '_self';
            } else if (!href.startsWith('http')) {
                // Internal links
                HTMLAttributes['target'] = '_self';
                HTMLAttributes['data-href'] = href;
                delete HTMLAttributes['href'];
            }
        }

        return [
            'a',
            mergeAttributes(this.options.HTMLAttributes as Record<string, unknown>, HTMLAttributes),
            0,
        ];
    },

    addProseMirrorPlugins() {
        return [
            ...(this.parent?.() ?? []),
            new Plugin({
                key: new PluginKey('customLinkClickHandler'),
                props: {
                    handleClick: (_view, _pos, event) => {
                        const target = event.target as HTMLElement;
                        const anchor = target.closest('a');
                        const internalHref = anchor?.dataset.href;

                        if (!internalHref) {
                            return false;
                        }

                        event.preventDefault();
                        this.options.onOpenFile?.(internalHref);

                        return true;
                    },
                },
            }),
        ];
    },
});
