// Service Worker for PTSP MTsN 2 Kota Malang
const CACHE_NAME = 'ptsp-mtsn2-v1.0.0';
const urlsToCache = [
    '/',
    '/welcome',
    '/css/app.css',
    '/js/app.js',
    '/images/logo.png',
    '/images/ptsp-og-image.jpg',
    '/favicon.ico'
];

// Install event - cache resources
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                console.log('Opened cache');
                return cache.addAll(urlsToCache);
            })
            .catch(function(error) {
                console.error('Failed to cache resources:', error);
            })
    );
});

// Fetch event - serve from cache when offline
self.addEventListener('fetch', function(event) {
    event.respondWith(
        caches.match(event.request)
            .then(function(response) {
                // Cache hit - return response
                if (response) {
                    return response;
                }

                // Clone the request
                var fetchRequest = event.request.clone();

                return fetch(fetchRequest).then(
                    function(response) {
                        // Check if valid response
                        if(!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }

                        // Clone the response
                        var responseToCache = response.clone();

                        caches.open(CACHE_NAME)
                            .then(function(cache) {
                                cache.put(event.request, responseToCache);
                            });

                        return response;
                    }
                ).catch(function() {
                    // Return a custom offline page if the network fails
                    if (event.request.mode === 'navigate') {
                        return caches.match('/offline.html') || new Response(
                            '<h1>Anda sedang offline</h1><p>Mohon periksa koneksi internet Anda.</p>',
                            { headers: { 'Content-Type': 'text/html' } }
                        );
                    }
                });
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Push notification handler (for future implementation)
self.addEventListener('push', function(event) {
    const options = {
        body: event.data ? event.data.text() : 'Notifikasi dari PTSP MTsN 2 Kota Malang',
        icon: '/images/logo.png',
        badge: '/images/badge.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Buka Aplikasi',
                icon: '/images/checkmark.png'
            },
            {
                action: 'close',
                title: 'Tutup',
                icon: '/images/xmark.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification('PTSP MTsN 2', options)
    );
});

// Background sync for offline form submissions
self.addEventListener('sync', function(event) {
    if (event.tag === 'background-sync-forms') {
        event.waitUntil(
            // Logic to sync form data when back online
            syncFormData()
        );
    }
});

function syncFormData() {
    // Implement form data synchronization logic
    return Promise.resolve();
}