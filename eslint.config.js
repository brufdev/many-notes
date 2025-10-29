import js from '@eslint/js';
import prettier from 'eslint-config-prettier';
import pluginVue from 'eslint-plugin-vue';
import globals from 'globals';
import ts from 'typescript-eslint';
import vueParser from 'vue-eslint-parser';

export default [
    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    ...ts.configs.recommended,
    prettier,
    {
        ignores: [
            'node_modules/**',
            'vendor/**',
            'public/**',
            'storage/**',
            'resources/views/**',
            'resources/js/actions/**',
            'resources/js/routes/**',
            'resources/js/types/**',
            'resources/js/wayfinder/**',
            '**/*.js',
        ],
    },
    {
        files: ['**/*.{ts,vue}'],
        languageOptions: {
            parser: vueParser,
            parserOptions: {
                parser: ts.parser,
                ecmaVersion: 'latest',
                sourceType: 'module',
                extraFileExtensions: ['.vue'],
            },
            globals: {
                ...globals.browser,
                ...globals.node,
            },
        },
        rules: {
            // Vue rules
            'vue/multi-word-component-names': 'off', // Disable the rule that requires multi-word component names in Vue
            'vue/component-api-style': ['error', ['script-setup', 'composition']], // Enforce specific API style for Vue components
            'vue/component-name-in-template-casing': ['error', 'PascalCase'], // Enforce PascalCase for component names in templates
            'vue/component-options-name-casing': ['error', 'PascalCase'], // Enforce PascalCase for component option names
            'vue/define-emits-declaration': ['error', 'type-based'], // Enforce type-based declarations for emits
            'vue/define-props-declaration': ['error', 'type-based'], // Enforce type-based declarations for props
            'vue/no-deprecated-slot-attribute': 'error', // Disallow the use of deprecated `slot` attribute
            'vue/no-deprecated-v-on-native-modifier': 'error', // Disallow the use of deprecated `v-on: native` modifier
            'vue/no-unused-refs': 'error', // Disallow unused template refs
            'vue/no-unused-vars': 'error',
            'vue/prefer-import-from-vue': 'error', // Prefer importing from Vue directly
            'vue/require-explicit-emits': 'error', // Require explicit emits declarations
            'vue/v-on-event-hyphenation': ['error', 'always', { autofix: true }], // Enforce hyphenation for event names in templates
            'vue/valid-define-props': 'error', // Ensure props definitions are valid
            'vue/require-prop-types': 'error',
            'vue/valid-v-memo': 'error', // Ensure usage of valid v-memo directive
            'vue/no-v-html': 'warn', // Warn about using v-html due to XSS risk
            'vue/attribute-hyphenation': ['error', 'always'], // Enforce kebab-case in HTML attributes
            'vue/no-template-shadow': 'error', // Prevent shadowed variables in template

            // JavaScript rules
            'no-unused-vars': 'off',
            'no-console': 'warn', // Warn about console.log (disable in production builds)
            'no-debugger': 'error', // Disallow debugger statements
            'eqeqeq': ['error', 'always'], // Enforce === and !==
            'no-var': 'error', // Disallow var, enforce let/const
            'prefer-const': 'error', // Use const where possible

            // TypeScript rules
            '@typescript-eslint/no-unused-vars': [
                'warn',
                {
                    argsIgnorePattern: '^_',
                    varsIgnorePattern: '^_',
                },
            ],
        },
    },
];
