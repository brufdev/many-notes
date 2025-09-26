export const hashtag = {
    name: 'hashtag',
    level: 'inline',
    start(src) {
        return src.match(/(^|\s)(\\?#[\p{L}0-9_-]+)/u)?.index;
    },
    tokenizer(src) {
        const rule = /^(\s?)(\\?#[\p{L}0-9_-]+)/u;
        const match = rule.exec(src);

        if (match) {
            // Don't parse inside code
            if (this.lexer.state.inCode) return;

            const leading = match[1];
            const fullTag = match[2];
            const escaped = fullTag.startsWith('\\');
            const text = escaped ? fullTag.slice(1) : fullTag;

            return {
                type: 'hashtag',
                raw: leading + fullTag,
                leading: match[1],
                text: text,
                escaped: escaped,
            };
        }
    },
    renderer(token) {
        return `${token.leading}<span data-hashtag="true" data-escaped="${token.escaped}">${token.text}</span>`;
    },
};
