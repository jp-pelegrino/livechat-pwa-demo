// Import Workbox from CDN
importScripts('https://storage.googleapis.com/workbox-cdn/releases/7.0.0/workbox-sw.js');

const { 
    registerRoute, 
    NavigationRoute, 
    Route 
} = workbox.routing;
const { 
    CacheFirst, 
    NetworkFirst, 
    StaleWhileRevalidate,
    NetworkOnly 
} = workbox.strategies;
const { 
    CacheableResponsePlugin 
} = workbox.cacheableResponse;
const { 
    ExpirationPlugin 
} = workbox.expiration;
const { 
    BackgroundSyncPlugin 
} = workbox.backgroundSync;

// Workbox configuration
workbox.core.setCacheNameDetails({
    prefix: 'livechat',
    suffix: 'v1',
    precache: 'precache',
    runtime: 'runtime'
});

// Precache static assets
workbox.precaching.precacheAndRoute([
    { url: '/', revision: '1' },
    { url: '/manifest.json', revision: '1' },
    { url: '/icon-192.png', revision: '1' },
    { url: '/icon-512.png', revision: '1' },
    { url: '/icon-120.png', revision: '1' },
    { url: '/icon-152.png', revision: '1' },
    { url: '/icon-167.png', revision: '1' },
    { url: '/icon-180.png', revision: '1' }
]);

// Cache images with CacheFirst strategy
registerRoute(
    ({ request }) => request.destination === 'image',
    new CacheFirst({
        cacheName: 'livechat-images',
        plugins: [
            new CacheableResponsePlugin({
                statuses: [0, 200],
            }),
            new ExpirationPlugin({
                maxEntries: 60,
                maxAgeSeconds: 30 * 24 * 60 * 60, // 30 Days
            }),
        ],
    })
);

// Cache CSS and JS with StaleWhileRevalidate strategy
registerRoute(
    ({ request }) => 
        request.destination === 'style' ||
        request.destination === 'script',
    new StaleWhileRevalidate({
        cacheName: 'livechat-assets',
        plugins: [
            new CacheableResponsePlugin({
                statuses: [0, 200],
            }),
        ],
    })
);

// Network first for HTML pages
registerRoute(
    ({ request }) => request.mode === 'navigate',
    new NetworkFirst({
        cacheName: 'livechat-pages',
        plugins: [
            new CacheableResponsePlugin({
                statuses: [0, 200],
            }),
        ],
    })
);

// Background sync for failed POST requests (e.g., messages)
// Workbox will automatically retry failed requests with exponential backoff
const bgSyncPlugin = new BackgroundSyncPlugin('message-queue', {
    maxRetentionTime: 24 * 60, // Retry for up to 24 hours (specified in minutes)
    // Workbox handles retries automatically with built-in exponential backoff
    // No need for custom onSync handler - let Workbox manage the queue
});

// Use NetworkOnly with background sync for API POST requests
registerRoute(
    ({ url }) => url.pathname.includes('/api/') && url.pathname.includes('/messages'),
    new NetworkOnly({
        plugins: [bgSyncPlugin],
    }),
    'POST'
);

// Push notification handler - works even when app is backgrounded
self.addEventListener('push', event => {
    console.log('Push notification received:', event);
    
    let data = { 
        title: 'New Message', 
        body: 'You have a new message',
        username: '',
        roomId: '',
        messageId: ''
    };
    
    if (event.data) {
        try {
            const payload = event.data.json();
            data = { ...data, ...payload };
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: '/icon-192.png',
        badge: '/icon-192.png',
        vibrate: [200, 100, 200],
        tag: data.messageId || 'chat-message',
        requireInteraction: false,
        silent: false,
        data: {
            url: data.url || '/',
            roomId: data.roomId,
            messageId: data.messageId,
            username: data.username,
            timestamp: Date.now()
        },
        actions: [
            { action: 'open', title: 'Open Chat', icon: '/icon-192.png' },
            { action: 'close', title: 'Dismiss' }
        ]
    };

    // Show notification and notify clients
    event.waitUntil(
        Promise.all([
            self.registration.showNotification(data.title, options),
            notifyClients({
                type: 'push-received',
                data: data
            })
        ])
    );
});

// Helper function to notify all clients
async function notifyClients(message) {
    const clients = await self.clients.matchAll({
        includeUncontrolled: true,
        type: 'window'
    });
    
    for (const client of clients) {
        client.postMessage(message);
    }
}

// Notification click handler - opens app when notification is clicked
self.addEventListener('notificationclick', event => {
    console.log('Notification clicked:', event);
    
    event.notification.close();

    if (event.action === 'close') {
        return;
    }

    const urlToOpen = event.notification.data.url || '/';
    const roomId = event.notification.data.roomId;
    
    event.waitUntil(
        clients.matchAll({ 
            type: 'window', 
            includeUncontrolled: true 
        }).then(clientList => {
            // First, try to find an existing window and focus it
            for (const client of clientList) {
                const clientUrl = new URL(client.url);
                const notificationUrl = new URL(urlToOpen, self.location.origin);
                
                if (clientUrl.origin === notificationUrl.origin) {
                    // Found a window from our origin, focus it
                    if ('focus' in client) {
                        client.focus();
                    }
                    // Notify the client to switch to the appropriate room
                    if (roomId) {
                        client.postMessage({
                            type: 'switch-room',
                            roomId: roomId
                        });
                    }
                    return;
                }
            }
            
            // No existing window found, open a new one
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

// Background Sync - handle message sending when back online
self.addEventListener('sync', event => {
    if (event.tag === 'sync-messages') {
        event.waitUntil(syncMessages());
    }
    // Workbox background sync plugin will handle 'message-queue' tag automatically
});

async function syncMessages() {
    // This will be triggered when the app comes back online
    // Notify clients to process their message queue
    const clients = await self.clients.matchAll({
        includeUncontrolled: true,
        type: 'window'
    });
    
    for (const client of clients) {
        client.postMessage({
            type: 'sync-messages'
        });
    }
}

// Handle messages from the client
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    } else if (event.data && event.data.type === 'CLAIM_CLIENTS') {
        self.clients.claim();
    }
});

// Periodic Background Sync (if supported) - for checking new messages
self.addEventListener('periodicsync', event => {
    if (event.tag === 'check-messages') {
        event.waitUntil(checkForNewMessages());
    }
});

async function checkForNewMessages() {
    // Notify all clients to check for new messages
    const clients = await self.clients.matchAll({
        includeUncontrolled: true,
        type: 'window'
    });
    
    for (const client of clients) {
        client.postMessage({
            type: 'check-messages'
        });
    }
}
