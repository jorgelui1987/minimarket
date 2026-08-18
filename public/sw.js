const CACHE = 'minimarket-v3';
// Solo archivos estáticos — NO rutas que redirigen (evita que falle la instalación)
const ASSETS = ['/manifest.json', '/icons/icon-192.png', '/icons/icon-512.png'];

self.addEventListener('install', (e) => {
  e.waitUntil(
    caches
      .open(CACHE)
      .then((c) => Promise.all(ASSETS.map((a) => c.add(a).catch(() => {}))))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (e) => {
  e.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (e) => {
  // Ignorar métodos que no sean GET
  if (e.request.method !== 'GET') return;
  // Ignorar peticiones a otros dominios (CDNs, APIs externas)
  const url = new URL(e.request.url);
  if (url.origin !== self.location.origin) return;
  // Ignorar peticiones de navegación — siempre red de red (la app requiere sesión)
  if (e.request.mode === 'navigate') return;

  // Estrategia: cache primero, red como respaldo (solo para assets estáticos)
  e.respondWith(
    caches.match(e.request).then((cached) => {
      const network = fetch(e.request)
        .then((res) => {
          if (res && res.status === 200) {
            const clone = res.clone();
            caches.open(CACHE).then((c) => c.put(e.request, clone));
          }
          return res;
        })
        .catch(() => cached);
      return cached || network;
    })
  );
});
