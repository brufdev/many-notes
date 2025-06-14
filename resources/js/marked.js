import { marked } from 'marked';

const renderer = new marked.Renderer();

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
                // Encode existing text token
                firstToken.tokens[0].text = encodeURIComponent(firstToken.tokens[0].text);
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

export const markedService = marked.setOptions({ renderer: renderer });
