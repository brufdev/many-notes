/** @type {import('prettier').Config & import('prettier-plugin-tailwindcss').PluginOptions} */
export default {
    semi: true,
    singleQuote: true,
    printWidth: 100,
    tabWidth: 4,
    trailingComma: 'es5',
    bracketSpacing: true,
    arrowParens: 'avoid',
    endOfLine: 'lf',
    vueIndentScriptAndStyle: false,
    singleAttributePerLine: false,

    plugins: ['prettier-plugin-organize-imports', 'prettier-plugin-tailwindcss'],
};
