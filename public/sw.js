/**
 * Service Worker WAG Presensi Digital.
 *
 * Aturan yang tidak boleh dilanggar:
 *  - Setiap push event WAJIB berakhir di showNotification(). Chrome akan menimpa
 *    dengan notifikasi generik bila tidak, dan iOS mencabut izin origin bila
 *    push diterima tanpa notifikasi tampil.
 *  - Jangan pernah menyetel silent: true — itu mematikan suara notifikasi.
 *  - renotify wajib tetap ada agar notifikasi dengan tag sama tetap berbunyi.
 */
'use strict';

try {
    importScripts('/js/push-util.js');
} catch (error) {
    // Kegagalan memuat util hanya berdampak pada pushsubscriptionchange.
    // Handler push tidak bergantung pada berkas ini.
    console.log('push-util failed to load', error);
}

function parsePayload(pushEvent) {
    if (!pushEvent || !pushEvent.data) {
        return {};
    }

    try {
        return pushEvent.data.json();
    } catch (error) {
        // Bukan JSON, lanjutkan sebagai teks biasa.
    }

    try {
        var text = pushEvent.data.text();
        if (!text) {
            return {};
        }

        try {
            return JSON.parse(text);
        } catch (error) {
            return { body: text };
        }
    } catch (error) {
        // Payload tidak terbaca sama sekali. Objek kosong membuat notifikasi
        // tetap tampil dengan judul dan isi default.
        return {};
    }
}

function buildOptions(data) {
    var tag = data.tag || ('presensi-' + (data.id || Date.now()));

    return {
        body: data.body || data.message || 'Ada notifikasi baru',
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
}

self.addEventListener('push', function (event) {
    event.waitUntil(
        (async function () {
            var data = parsePayload(event.data);
            var options = buildOptions(data);

            try {
                await self.registration.showNotification(
                    data.title || 'Presensi Digital',
                    options
                );
            } catch (error) {
                // Permission ditolak atau antarmuka bermasalah. Ditangkap agar
                // promise yang diberikan ke waitUntil tetap resolve.
                console.log('showNotification failed', error);
            }
        })()
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    var data = event.notification.data || {};
    var url = data.url || '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(function (clientList) {
                for (var i = 0; i < clientList.length; i++) {
                    if (clientList[i].url.includes(self.registration.scope) && 'focus' in clientList[i]) {
                        clientList[i].navigate(url);
                        return clientList[i].focus();
                    }
                }

                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
    );
});

/**
 * Event sekunder. Self-heal utama tetap sinkronisasi saat page load dan
 * visibilitychange (lihat window.WAGPush pada layouts/presensi.blade.php).
 * Segala kegagalan di sini hanya dicatat dan tidak pernah merambat ke handler push.
 */
self.addEventListener('pushsubscriptionchange', function (event) {
    event.waitUntil(
        resyncSubscription(event).catch(function (error) {
            console.log('pushsubscriptionchange failed', error);
        })
    );
});

async function resyncSubscription(event) {
    if (!self.WAGPushUtil) {
        throw new Error('push_util_unavailable');
    }

    // GET tidak divalidasi CSRF, sehingga Service Worker tidak perlu menyimpan
    // token di localStorage maupun membaca cookie secara manual.
    var bootstrapResponse = await fetch('/api/push/bootstrap', {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    });

    if (!bootstrapResponse.ok) {
        throw new Error('bootstrap_' + bootstrapResponse.status);
    }

    var bootstrap = await bootstrapResponse.json();

    if (!bootstrap.public_key || !bootstrap.csrf) {
        throw new Error('bootstrap_incomplete');
    }

    var registration = self.registration;
    var subscription = event.newSubscription;

    if (!subscription) {
        subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: self.WAGPushUtil.urlBase64ToUint8Array(bootstrap.public_key)
        });
    }

    var keys = subscription.toJSON().keys;
    var response = await fetch('/api/push/subscribe', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': bootstrap.csrf
        },
        body: JSON.stringify({
            endpoint: subscription.endpoint,
            public_key: keys.p256dh,
            auth_token: keys.auth
        })
    });

    if (!response.ok) {
        throw new Error('subscribe_' + response.status);
    }
}
