const CACHE_NAME = 'many-notes-v1';
const urlsToCache = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/assets/icon-16x16.png',
    '/assets/icon-32x32.png',
    '/assets/icon-72x72.png',
    '/assets/icon-96x96.png',
    '/assets/icon-128x128.png',
    '/assets/icon-144x144.png',
    '/assets/icon-152x152.png',
    '/assets/icon-180x180.png',
    '/assets/icon-192x192.png',
    '/assets/icon-384x384.png',
    '/assets/icon-512x512.png',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Return cached version or fetch from network
                return response || fetch(event.request);
            })
    );
});
