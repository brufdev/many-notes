import { InputRule, mergeAttributes } from '@tiptap/core';
import Link from '@tiptap/extension-link';

const angleBracketLinkRule = function (type) {
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

export const CustomLink = Link.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            'data-angle-bracket': {
                default: null,
                parseHTML: element => element.getAttribute('data-angle-bracket'),
                renderHTML: attributes => {
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
                parseHTML: element => element.getAttribute('title'),
                renderHTML: attributes => {
                    if (!attributes.title) {
                        return {};
                    }

                    return {
                        title: attributes.title,
                    };
                },
            },
        };
    },
    addPasteRules() {
        return [];
    },
    addInputRules() {
        return [
            angleBracketLinkRule(this.type),
        ];
    },
    renderHTML({ HTMLAttributes }) {
        const { href } = HTMLAttributes;

        if (href.match(/^[^>]+@[^>]+.[^>]+$/)) {
            // Process email links
            HTMLAttributes.target = '_self';
        } else if (!href.startsWith('http')) {
            // Process internal links
            HTMLAttributes.target = '_self';
            HTMLAttributes['wire:click.prevent'] = `openFilePath('${href}')`;
            HTMLAttributes['data-href'] = href;
            delete HTMLAttributes.href;
        }

        return ['a', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
    },
});
