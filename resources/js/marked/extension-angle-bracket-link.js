export const angleBracketLink = {
    name: 'angleBracketLink',
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
                    type: 'angleBracketLink',
                    raw: match[0],
                    href: match[1],
                    variant: 'url',
                };
            }
        } catch (error) {
        }

        // Process email links
        rule = /^&lt;([^>\s]+@[^>\s]+\.[^>\s]+)>/;
        match = rule.exec(src);
        console.log('match', match);

        if (match) {
            return {
                type: 'angleBracketLink',
                raw: match[0],
                href: match[1],
                variant: 'email',
            };
        }
    },
    renderer(token) {
        const href = token.variant === 'email' ? `mailto:${token.href}` : token.href;

        return `<a href="${href}" data-angle-bracket="true">${token.href}</a>`;
    },
};
