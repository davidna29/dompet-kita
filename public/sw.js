const CACHE = 'dompet-kita-static-v2';
const STATIC = ['/offline.html', '/icons/favicon.svg', '/icons/icon-192.png', '/icons/icon-512.png', '/manifest.webmanifest'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        (async () => {
            const cache = await caches.open(CACHE);
            await cache.addAll(STATIC);

            try {
                const manifest = await fetch('/build/manifest.json', { cache: 'no-store' }).then((response) => response.json());
                const files = new Set();

                Object.values(manifest).forEach((entry) => {
                    if (entry.file) {
                        files.add('/build/' + entry.file);
                    }
                    (entry.css || []).forEach((file) => files.add('/build/' + file));
                    (entry.assets || []).forEach((file) => files.add('/build/' + file));
                });

                await Promise.allSettled([...files].map((file) => cache.add(file)));
            } catch {
                // The build manifest is optional; navigation still works online.
            }
        })()
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Never cache pages or API data. Navigation falls back to a static offline notice only.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match('/offline.html')));
        return;
    }

    // Only the built shell assets and icons are cacheable.
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/') || url.pathname === '/manifest.webmanifest') {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) {
                    return cached;
                }
                return fetch(request).then((response) => {
                    if (!response || response.status !== 200) {
                        return response;
                    }
                    const salinan = response.clone();
                    caches.open(CACHE).then((cache) => cache.put(request, salinan));
                    return response;
                });
            })
        );
    }
});
