import { type Token, type Tokens } from 'marked';

type AngleBracketLinkVariant = 'url' | 'email';

interface AngleBracketLinkToken {
    type: 'angleBracketLink';
    raw: string;
    href: string;
    variant: AngleBracketLinkVariant;
}

export const angleBracketLink = {
    name: 'angleBracketLink',
    level: 'inline' as const,

    start(src: string): number | undefined {
        return /&lt;/.exec(src)?.index;
    },

    tokenizer(src: string, _tokens: Token[]): AngleBracketLinkToken | undefined {
        // Process external links
        const urlRule = /^&lt;(https?:\/\/[^>]+)>/;
        const urlMatch = urlRule.exec(src);

        if (urlMatch) {
            try {
                new URL(urlMatch[1]);

                return {
                    type: 'angleBracketLink',
                    raw: urlMatch[0],
                    href: urlMatch[1],
                    variant: 'url',
                };
            } catch {
                // Not a valid URL, fall through to email check
            }
        }

        // Process email links
        const emailRule = /^&lt;([^>\s]+@[^>\s]+\.[^>\s]+)>/;
        const emailMatch = emailRule.exec(src);

        if (emailMatch) {
            return {
                type: 'angleBracketLink',
                raw: emailMatch[0],
                href: emailMatch[1],
                variant: 'email',
            };
        }

        return undefined;
    },

    renderer(token: Tokens.Generic): string {
        const { href, variant } = token as AngleBracketLinkToken;
        const resolvedHref = variant === 'email' ? `mailto:${href}` : href;

        return `<a href="${resolvedHref}" data-angle-bracket="true">${href}</a>`;
    },
};
