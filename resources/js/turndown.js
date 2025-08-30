import TurndownService from 'turndown';
import { highlightedCodeBlock, strikethrough, tables, taskListItems } from '@guyplusplus/turndown-plugin-gfm';

TurndownService.prototype.escape = function (string) {
    return string;
};

export const turndownService = new TurndownService({
    headingStyle: 'atx',
    hr: '---',
    bulletListMarker: '-',
    codeBlockStyle: 'fenced',
    emDelimiter: '*',
    linkReferenceStyle: 'shortcut',
}).use([
    highlightedCodeBlock,
    strikethrough,
    tables,
    taskListItems,
]).addRule('listItem', {
    filter: 'li',
    replacement: function (content, node, options) {
        content = content
            .replace(/^\n+/, '') // Remove leading newlines
            .replace(/\n+$/, '\n') // Replace trailing newlines with just a single one
            .replace(/\n/gm, '\n    '); // Indent
        let prefix = options.bulletListMarker + ' ';
        const parent = node.parentNode;

        if (parent.nodeName === 'OL') {
            const start = parent.getAttribute('start');
            const index = Array.prototype.indexOf.call(parent.children, node);
            prefix = (start ? Number(start) + index : index + 1) + '. ';
        }

        const isTaskList = content.startsWith('[ ]') || content.startsWith('[x]');

        // Remove lines containing only whitespaces
        if (!isTaskList) {
            content = content.replace(/^\s+$\n?/gm, '');
        }

        return prefix + content + (node.nextSibling && !content.endsWith('\n') ? '\n' : '');
    },
}).addRule('image', {
    filter: 'img',
    replacement: function (content, node) {
        const alt = node.getAttribute('alt');
        const src = node.getAttribute('src')?.replace(/^\/files\/\d+\?path=/, '');
        const title = node.getAttribute('title');
        const titlePart = title ? ` "${title}"` : '';

        if (!src) {
            return content;
        }

        try {
            return `![${alt}](${decodeURI(src)}${titlePart})`;
        } catch (error) {
            return `![${alt}](${src}${titlePart})`;
        }
    },
}).addRule('decodeLinks', {
    filter: 'a',
    replacement: function(content, node) {
        const href = node.getAttribute('href');
        const title = node.getAttribute('title');
        const titlePart = title ? ` "${title}"` : '';

        if (!href) {
            return content;
        }
        
        try {
            return `[${content}](${decodeURI(href)}${titlePart})`;
        } catch (error) {
            return `[${content}](${href}${titlePart})`;
        }
    }
});
