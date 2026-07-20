{{-- Served as application/javascript by the push.service-worker route. --}}
importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: @json($config['api_key'] ?? ''),
    authDomain: @json($config['auth_domain'] ?? ''),
    projectId: @json($config['project_id'] ?? ''),
    storageBucket: @json($config['storage_bucket'] ?? ''),
    messagingSenderId: @json($config['messaging_sender_id'] ?? ''),
    appId: @json($config['app_id'] ?? ''),
});

const messaging = firebase.messaging();

// Fires only when the app is closed or in another tab — foreground messages are
// handled in-page by partials/push.blade.php so they can raise a toast instead.
messaging.onBackgroundMessage(function (payload) {
    const data = payload.data || {};
    const notification = payload.notification || {};

    self.registration.showNotification(notification.title || {!! json_encode(config('app.name')) !!}, {
        body: notification.body || '',
        icon: data.icon || {!! json_encode(asset('images/pwa-192.png')) !!},
        badge: data.icon || {!! json_encode(asset('images/pwa-192.png')) !!},
        data: { url: data.url || '/' },
    });
});

// Focus an already-open tab rather than piling up new ones.
self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const target = (event.notification.data && event.notification.data.url) || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (windows) {
            for (const client of windows) {
                if (client.url === target && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(target);
            }
        })
    );
});
