const CACHE_NAME = 'vextra-shell-v2';
const APP_SHELL = [
  '/iniciar-sesion',
  '/site.webmanifest',
  '/assets/css/app.css',
  '/assets/js/app.js',
  '/img/logo/logo_vextra.png',
  '/img/logo/icono.png',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL)).catch(() => null)
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        const copy = response.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy)).catch(() => null);
        return response;
      })
      .catch(() =>
        caches.match(event.request).then(
          (cached) => cached || new Response('Sin conexión', { status: 503, statusText: 'Offline' })
        )
      )
  );
});
