// sw.js

const CACHE_NAME = 'crm-ventas-cache-v1';
// Lista de archivos que queremos guardar en caché para que la app funcione sin conexión.
const urlsToCache = [
  '/',
  '/index.php',
  '/dashboard.php',
  '/login.php',
  '/offline.php', // Una página para mostrar cuando no hay conexión
  '/assets/css/style.css',
  'https://cdn.tailwindcss.com',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css'
];

// Evento 'install': Se dispara cuando el Service Worker se instala por primera vez.
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Cache abierto');
        return cache.addAll(urlsToCache);
      })
  );
});

// Evento 'fetch': Se dispara cada vez que la página solicita un recurso (un archivo, una imagen, etc.).
self.addEventListener('fetch', event => {
  event.respondWith(
    // Intenta buscar el recurso en la caché primero.
    caches.match(event.request)
      .then(response => {
        // Si lo encuentra en la caché, lo devuelve.
        if (response) {
          return response;
        }
        // Si no, lo busca en la red.
        return fetch(event.request).catch(() => {
            // Si la red también falla, muestra la página offline.
            return caches.match('/offline.php');
        });
      })
  );
});

// Evento 'activate': Limpia cachés antiguas si hemos actualizado la versión.
self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});
