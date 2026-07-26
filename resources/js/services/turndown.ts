import { highlightedCodeBlock, strikethrough, tables } from '@guyplusplus/turndown-plugin-gfm';
import TurndownService from 'turndown';

TurndownService.prototype.escape = (string: string): string => string;

export const turndownService = new TurndownService({
    headingStyle: 'atx',
    hr: '---',
    bulletListMarker: '-',
    codeBlockStyle: 'fenced',
    emDelimiter: '*',
    linkReferenceStyle: 'shortcut',
})
    .use([highlightedCodeBlock, strikethrough, tables])
    .addRule('listItem', {
        filter: 'li',
        replacement(content: string, node: Node, options: TurndownService.Options): string {
            const element = node as HTMLLIElement;

            let prefix = (options.bulletListMarker ?? '-') + ' ';
            const parent = element.parentNode as HTMLElement | null;

            if (parent?.nodeName === 'OL') {
                const olParent = parent as HTMLOListElement;
                const start = olParent.getAttribute('start');
                const index = Array.prototype.indexOf.call(olParent.children, element);
                prefix = (start ? Number(start) + index : index + 1) + '. ';
            }

            const indent = ' '.repeat(prefix.length);

            if (element.dataset.type === 'taskItem') {
                prefix += element.dataset.checked === 'true' ? '[x] ' : '[ ] ';
            }

            content = content
                .replace(/^\n+/, '') // Remove leading newlines
                .replace(/\n+$/, '\n') // Replace trailing newlines with just one
                .replace(/\n/gm, '\n' + indent) // Indent nested content
                .replace(/^[^\S\n]+$\n?/gm, ''); // Remove lines containing only whitespaces

            return prefix + content + (element.nextSibling && !content.endsWith('\n') ? '\n' : '');
        },
    })
    .addRule('image', {
        filter: 'img',
        replacement(content: string, node: Node): string {
            const element = node as HTMLImageElement;
            const alt = element.getAttribute('alt') ?? '';
            const src = element.getAttribute('src')?.replace(/^\/files\/\d+\?path=/, '');
            const title = element.getAttribute('title');
            const titlePart = title ? ` "${title}"` : '';

            if (!src) {
                return content;
            }

            try {
                return `![${alt}](${decodeURI(src)}${titlePart})`;
            } catch {
                return `![${alt}](${src}${titlePart})`;
            }
        },
    })
    .addRule('link', {
        filter: 'a',
        replacement(content: string, node: Node): string {
            const element = node as HTMLAnchorElement;
            const href = element.getAttribute('href');
            const title = element.getAttribute('title');
            const titlePart = title ? ` "${title}"` : '';
            const autoLink = element.classList.contains('autoLink');
            const angleBracketLink = element.dataset.angleBracket === 'true';

            if (!href) {
                return content;
            }

            let cleanHref: string;

            try {
                cleanHref = decodeURI(href);
            } catch {
                cleanHref = href;
            }

            cleanHref = cleanHref.replace(/^mailto:/, '');

            if (autoLink) {
                return cleanHref;
            }

            if (angleBracketLink) {
                return `<${cleanHref}>`;
            }

            return `[${content}](${cleanHref}${titlePart})`;
        },
    })
    .addRule('hashtag', {
        filter: (node: Node): boolean => {
            const element = node as HTMLElement;

            return element.nodeName === 'SPAN' && element.dataset.hashtag === 'true';
        },
        replacement(content: string, node: Node): string {
            const element = node as HTMLElement;
            const escaped = element.dataset.escaped === 'true';

            return escaped ? `\\${content}` : content;
        },
    });
