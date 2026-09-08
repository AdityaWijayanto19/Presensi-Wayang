// Service Worker for Push Notifications
self.addEventListener('push', function(event) {
    if (event.data) {
        const data = event.data.json();
        const tag = data.tag || ('presensi-' + (data.id || Date.now()));
        const options = {
            body: data.body || data.message || 'New notification',
            icon: '/icons/icon_192.png',
            badge: '/icons/icon_192.png',
            vibrate: [100, 50, 100],
            tag: tag,
            renotify: true,
            data: {
                url: data.url || '/dashboard',
                id: data.id,
                tag: tag
            },
            actions: [
                { action: 'open', title: 'Buka' }
            ]
        };
        event.waitUntil(
            self.registration.showNotification(data.title || 'Presensi Digital', options)
        );
    }
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    const url = event.notification.data?.url || '/dashboard';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(function(clientList) {
                for (const client of clientList) {
                    if (client.url.includes(self.registration.scope) && 'focus' in client) {
                        client.navigate(url);
                        return client.focus();
                    }
                }
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
    );
});

self.addEventListener('notificationclose', function(event) {
    // Optional: track closed notifications
});
