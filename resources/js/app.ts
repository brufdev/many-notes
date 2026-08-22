import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import './bootstrap';

createInertiaApp({
    pages: './pages',
    title: title => (title ? `${title} - Many Notes` : 'Many Notes'),
    withApp(app) {
        app.use(createPinia());
    },
});
