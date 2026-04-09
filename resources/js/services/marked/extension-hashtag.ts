import { type Token, type Tokens } from 'marked';

interface HashtagToken {
    type: 'hashtag';
    raw: string;
    leading: string;
    text: string;
    escaped: boolean;
}

export const hashtag = {
    name: 'hashtag',
    level: 'inline' as const,

    start(src: string): number | undefined {
        return /(^|\s)(\\?#[\p{L}0-9_-]+)/u.exec(src)?.index;
    },

    tokenizer(
        this: { lexer: { state: { inCode: boolean } } },
        src: string,
        _tokens: Token[]
    ): HashtagToken | undefined {
        const match = /^(\s?)(\\?#[\p{L}0-9_-]+)/u.exec(src);

        if (!match) {
            return undefined;
        }

        // Don't parse inside code
        if (this.lexer.state.inCode) {
            return undefined;
        }

        const leading = match[1];
        const fullTag = match[2];
        const escaped = fullTag.startsWith('\\');
        const text = escaped ? fullTag.slice(1) : fullTag;

        return {
            type: 'hashtag',
            raw: leading + fullTag,
            leading,
            text,
            escaped,
        };
    },

    renderer(token: Tokens.Generic): string {
        const { leading, escaped, text } = token as HashtagToken;

        return `${leading}<span data-hashtag="true" data-escaped="${escaped}">${text}</span>`;
    },
};
