const cacheName = 'many-notes-v1';
const cachePaths = [
    '/css/app.css',
    '/js/app.js',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(cacheName).then(cache => cache.addAll(cachePaths)),
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => {
            // Return cached version or fetch from network
            return response || fetch(event.request);
        }),
    );
});
