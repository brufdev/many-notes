declare module '@guyplusplus/turndown-plugin-gfm' {
    import TurndownService from 'turndown';
    type Plugin = (service: TurndownService) => void;
    export const highlightedCodeBlock: Plugin;
    export const strikethrough: Plugin;
    export const tables: Plugin;
    export const taskListItems: Plugin;
}
