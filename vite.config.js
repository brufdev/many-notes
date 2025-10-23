import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: [
                'robots.txt',
                'assets/*',
            ],
            manifest: {
                name: "Many Notes",
                short_name: "ManyNotes",
                description: "Markdown note-taking web application designed for simplicity.",
                start_url: "/",
                scope: "/",
                display: "standalone",
                background_color: "#ffffff",
                theme_color: "#000000",
                orientation: "any",
                icons: [
                    { src: "/assets/icon-192x192.png", sizes: "192x192", type: "image/png", purpose: "any" },
                    { src: "/assets/icon-256x256.png", sizes: "256x256", type: "image/png", purpose: "any" },
                    { src: "/assets/icon-384x384.png", sizes: "384x384", type: "image/png", purpose: "any" },
                    { src: "/assets/icon-512x512.png", sizes: "512x512", type: "image/png", purpose: "any" },

                    { src: "/assets/icon-192x192-maskable.png", sizes: "192x192", type: "image/png", purpose: "maskable" },
                    { src: "/assets/icon-512x512-maskable.png", sizes: "512x512", type: "image/png", purpose: "maskable" },
                ]
            },
            workbox: {
                globPatterns: ['**/*.{js,css,png,svg}'],
                cleanupOutdatedCaches: true,
                clientsClaim: true,
                skipWaiting: true,
                navigateFallback: null,
            },
        }),
    ],
});
