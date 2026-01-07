// sw.js (robusto para instalar incluso si algún recurso falla)
const CACHE_NAME = 'modem-config-v1';

// Rutas relativas al scope. Ajustá el array a los recursos que existan realmente.
const FILES_TO_CACHE = [
  'index.html',
  'main.js',
  'manifest.json',
  'icon.png',
  'icon-512.png',
  // NO incluyas rutas de "carpeta" como '' o '/' o 'dispatch/installer/alt/'.
];

self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing...');
  event.waitUntil(
    (async () => {
      const cache = await caches.open(CACHE_NAME);
      const base = new URL(self.registration.scope).href; // base absoluta del scope
      console.log('[Service Worker] Base scope:', base);

      // Intentamos cachear cada recurso de forma individual y no abortamos todo si falla uno.
      const results = await Promise.allSettled(
        FILES_TO_CACHE.map(async (relPath) => {
          const url = new URL(relPath, base).href;
          try {
            const resp = await fetch(url, { cache: 'no-cache' });
            if (!resp.ok) throw new Error(`HTTP ${resp.status} for ${url}`);
            await cache.put(url, resp.clone());
            console.log('[Service Worker] Cached:', url);
            return { url, ok: true };
          } catch (err) {
            console.warn('[Service Worker] Skip caching (failed):', url, err);
            return { url, ok: false, error: String(err) };
          }
        })
      );

      const failed = results.filter(r => r.status === 'fulfilled' && !r.value.ok);
      if (failed.length) {
        console.warn('[Service Worker] Algunos recursos no pudieron cachearse:', failed.map(f => f.value?.url || f));
      } else {
        console.log('[Service Worker] Todos los recursos cacheados correctamente.');
      }
    })()
  );
  // activar inmediatamente la nueva versión
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activating...');
  event.waitUntil(
    (async () => {
      const keys = await caches.keys();
      await Promise.all(
        keys.map((k) => {
          if (k !== CACHE_NAME) {
            console.log('[Service Worker] Deleting old cache:', k);
            return caches.delete(k);
          }
        })
      );
      // controlar clients
      await self.clients.claim();
    })()
  );
});

self.addEventListener('fetch', (event) => {
  const requestUrl = new URL(event.request.url);

  // dejar pasar peticiones al modem local (IP 192.168.1.1) para comunicación en vivo
  if (event.request.url.includes('http://192.168.1.1') || event.request.url.includes('https://192.168.1.1')) {
    return event.respondWith(fetch(event.request).catch(err => {
      console.warn('[Service Worker] Modem fetch failed, returning network error:', err);
      return new Response('Modem unreachable', { status: 503, statusText: 'Modem unreachable' });
    }));
  }

  // Para navegación (document) preferimos el caché y caemos al network si no existe:
  if (event.request.mode === 'navigate') {
    event.respondWith(
      caches.match(event.request).then((cached) => {
        if (cached) {
          return cached;
        }
        return fetch(event.request)
          .then((resp) => {
            // opcional: almacenar en cache dinámico las páginas HTML si conviene
            return resp;
          })
          .catch(() => {
            // fallback a página offline si existe
            return caches.match('index.html');
          });
      })
    );
    return;
  }

  // Para otros recursos: primero caché, si no existe -> network -> fallback a index.html
  event.respondWith(
    caches.match(event.request).then((cachedResp) => {
      if (cachedResp) {
        // console.log('[Service Worker] Serving from cache:', event.request.url);
        return cachedResp;
      }
      return fetch(event.request).then((networkResp) => {
        // opcional: cachear respuestas estáticas o imágenes aquí si se desea
        return networkResp;
      }).catch(() => {
        // fallback: si es petición a un recurso que queremos tener offline, devolver index.html
        return caches.match('index.html');
      });
    })
  );
});
