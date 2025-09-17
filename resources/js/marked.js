import { marked } from 'marked';

const angledLinkExtension = {
    name: 'angledLink',
    level: 'inline',
    start(src) {
        return src.match(/&lt;/)?.index;
    },
    tokenizer(src, tokens) {
        // Process external links
        let rule = /^&lt;(https?:\/\/[^>]+)>/;
        let match = rule.exec(src);

        try {
            if (match && new URL(match[1])) {
                return {
                    type: 'angledLink',
                    raw: match[0],
                    href: match[1],
                };
            }
        } catch (error) {
        }

        // Process email links
        rule = /^&lt;([^>\s]+@[^>\s]+\.[^>\s]+)>/;
        match = rule.exec(src);

        if (match) {
            return {
                type: 'angledLink',
                raw: match[0],
                href: match[1],
            };
        }
    },
    renderer(token) {
        return `<a href="${token.href}" class="angledLink">${token.href}</a>`;
    },
};

const renderer = new marked.Renderer();

renderer.link = function({ href, raw, text, title }) {
    // Skip autolinking URLs/emails in plain Markdown text
    if (raw === text) {
        return text;
    }

    const classAttr = raw === href ? ' class="autoLink"' : '';
    const titleAttr = title ? ` title="${title}"` : '';

    return `<a href="${href}"${classAttr}${titleAttr}>${text}</a>`;
};

renderer.list = function(token) {
    const ordered = token.ordered;
    const taskList = token.items[0]?.task === true;
    const start = token.start;
    const type = ordered ? 'ol' : 'ul';
    let body = '';

    for (const item of token.items) {
        body += this.listitem(item);
    }

    let startAttr = '';

    if (taskList) {
        startAttr = ' data-type="taskList"';
    } else if (ordered && start !== 1) {
        startAttr = ` start="${start}"`;
    }

    return `<${type}${startAttr}>\n${body}</${type}>\n`;
};

renderer.listitem = function(item) {
    let itemAttr = '';

    if (item.task) {
        const checkedAttr = item.checked ? 'true' : 'false';
        itemAttr = ` data-type="taskItem" data-checked="${checkedAttr}"`;

        if (item.loose) {
            const firstToken = item.tokens[0];
            const hasParagraphWithText = firstToken?.type === 'paragraph'
                && firstToken.tokens?.length > 0
                && firstToken.tokens[0].type === 'text';

            if (hasParagraphWithText) {
                firstToken.tokens[0].escaped = true;
            } else {
                // Prepend empty text token
                item.tokens.unshift({
                    type: 'text',
                    raw: '',
                    text: '',
                    escaped: true,
                });
            }
        }
    }

    const itemBody = this.parser.parse(item.tokens, !!item.loose);

    return `<li${itemAttr}>${itemBody}</li>\n`;
};

renderer.code = function({ text, lang }) {
    const langClass = lang ? ` class="language-${lang}"` : '';

    return `<pre><code${langClass}>${text}</code></pre>`;
};

renderer.codespan = function({ text }) {
    return `<code>${text}</code>`;
};

marked.use({ extensions: [angledLinkExtension] });

export const markedService = marked.setOptions({ renderer: renderer });
