import { createInertiaApp, router } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import './bootstrap';
import { hydrateStoresFromPageProps } from './inertia/hydrateStores.js';

createInertiaApp({
    pages: './pages',
    title: title => (title ? `${title} - Many Notes` : 'Many Notes'),
    withApp(app, { page }) {
        app.use(createPinia());

        hydrateStoresFromPageProps(page.props);

        router.on('success', event => {
            hydrateStoresFromPageProps(event.detail.page.props);
        });
    },
});
