# Contributing

Before jumping in, please open a discussion to talk through the idea first. It helps make sure it fits the project and avoids wasted time or overlap. The goal is to keep the project simple to run and easy to use with each release, so it's important to think carefully about each new feature.

When suggesting or submitting new features, consider whether they’re likely to be useful to others. Open source projects often serve a wide range of users with different needs. Try to think beyond your own use case.

## Basics

- Pull requests should target the `dev` branch
- Changes must pass the full test suite before being reviewed
- One pull request per feature or fix
- Update documentation if your change affects how the project is used
- Add tests for any new features
- Use clear, focused commits with meaningful messages
- Follow the existing coding style

## Code style

### PHP

Please run `composer ci` before you open a pull request. It is the same command CI runs, so if it passes locally it should pass there too.

It bundles a few things:

- `composer rector` and `composer pint` check code style
- `composer phpcs` checks PSR-12
- `composer phpstan` runs static analysis (level 10)
- `composer test:type-coverage` runs the type coverage tests (100% required)
- `composer test` runs the Pest test suite

If style checks complain, `composer fix` will fix what it can automatically. To run just the tests, use `composer test`.

### Frontend

CI runs all three of these, so please run them before you open a pull request:

- `npm run lint` for ESLint
- `npm run format` for Prettier
- `npm run typecheck` for TypeScript and Vue types

The first two have a matching fix command: `npm run lint:fix` and `npm run format:fix`.

If typecheck says it cannot find `@/actions/...` or `@/routes/...`, run `npm run build` to generate them.

### Vue components

ESLint takes care of two things for you. Blocks go in the order `<script>`, `<template>`, `<style>`. Compiler macros go at the top of `<script setup>`, in this order: `defineOptions`, `defineModel`, `defineProps`, `defineEmits`, `defineSlots`. `defineExpose` goes last, at the bottom of the block.

Below the macros, we group declarations so that nothing depends on something declared further down:

1. Compiler macros: `defineProps`, `defineEmits`, `defineModel`
2. Injected context: `usePage()`, `inject()`, `useSlots()`
3. Pinia stores: `useVaultStore()`, `useLayoutStore()`
4. Shared composables: `useModalManager()`, `useToast()`, `useScreenSize()`
5. Local state: `ref()`, `reactive()`, template refs, `useRequest()`
6. Derived state: `computed()` and plain derived constants
7. Functions and event handlers
8. Watchers
9. Lifecycle hooks: `onMounted()`, `onUnmounted()`
10. `provide()` and `defineExpose()`

Steps 2 to 10 are a convention rather than a lint rule, so it is something to watch for in review. Sometimes the order genuinely cannot work and that is fine.

## How to contribute

1. Fork the repository
2. Create a new branch from `dev`
3. Make your changes with focused commits
4. Add tests, run `composer test`, and make sure it passes
5. Submit a pull request to the `dev` branch

## Code of Conduct

As the maintainer, it is my responsibility to ensure that all submissions meet the project's quality standards and goals. If you are contributing, I assume good intent and hope for straightforward and constructive interactions.
