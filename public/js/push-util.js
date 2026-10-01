/**
 * Util Web Push yang dipakai bersama oleh halaman dan Service Worker.
 * Service Worker tidak punya akses ke DOM sehingga logika ini tidak boleh
 * didefinisikan di dalam Blade.
 */
self.WAGPushUtil = (function () {
    'use strict';

    /**
     * Mengubah VAPID public key (base64url) menjadi Uint8Array
     * sesuai kebutuhan opsi PushManager.subscribe({ applicationServerKey }).
     */
    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - base64String.length % 4) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var rawData = self.atob(base64);
        var output = new Uint8Array(rawData.length);

        for (var i = 0; i < rawData.length; ++i) {
            output[i] = rawData.charCodeAt(i);
        }

        return output;
    }

    return {
        urlBase64ToUint8Array: urlBase64ToUint8Array
    };
})();
