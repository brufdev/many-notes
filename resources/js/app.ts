import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import './bootstrap.js';
import { hydrateStoresFromPageProps } from './inertia/hydrateStores.js';

createInertiaApp({
    resolve: name =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        const app = createApp(h(App, props));

        app.use(plugin);
        app.use(pinia);

        hydrateStoresFromPageProps(props.initialPage.props);

        router.on('success', event => {
            hydrateStoresFromPageProps(event.detail.page.props);
        });

        app.mount(el);
    },
});
