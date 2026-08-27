const CACHE_NAME = 'rems-transport-v1';

// App shell — static assets cached immediately
const SHELL_ASSETS = [
    '/',
    '/css/style.css',
    '/css/admin.css',
    '/css/booking.css',
    '/css/my-bookings.css',
    '/css/profile.css',
    '/css/login.css',
    '/css/forgot.css',
    '/css/register.css',
    '/css/responsive.css',
    '/js/script.js',
    '/js/booking.js',
    '/js/pwa.js',
    '/icons/icon-192.svg',
    '/icons/icon-512.svg',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
];

// Install: cache shell
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(SHELL_ASSETS).catch(() => {}))
    );
    self.skipWaiting();
});

// Activate: clear old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Fetch: cache-first for static assets, network-first for pages/API
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET and cross-origin except CDN fonts/icons
    if (request.method !== 'GET') return;
    if (url.origin !== location.origin &&
        !url.hostname.includes('cdnjs.cloudflare.com') &&
        !url.hostname.includes('fonts.googleapis.com') &&
        !url.hostname.includes('fonts.gstatic.com')) return;

    // Static assets → cache-first
    if (url.pathname.match(/\.(css|js|svg|png|jpg|jpeg|gif|ico|woff2?|ttf)$/)) {
        event.respondWith(
            caches.match(request).then(cached => cached || fetch(request).then(resp => {
                const clone = resp.clone();
                caches.open(CACHE_NAME).then(c => c.put(request, clone));
                return resp;
            }))
        );
        return;
    }

    // Pages → network-first, fall back to cache
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});
