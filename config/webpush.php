<?php

return [
    'enabled' => env('WEB_PUSH_ENABLED', true),

    // Berapa lama push service (FCM/APNs) menyimpan pesan untuk perangkat offline
    // sebelum membuangnya. 86400 = 24 jam.
    'ttl' => (int) env('WEB_PUSH_TTL', 86400),

    // 'high' agar pesan tetap dikirim saat Android dalam mode Doze.
    // Nilai yang valid mengikuti Web Push Protocol: very-low, low, normal, high.
    'urgency' => env('WEB_PUSH_URGENCY', 'high'),

    'vapid' => [
        'subject' => env('WEB_PUSH_SUBJECT', 'mailto:admin@presensi.com'),
        'public_key' => env('WEB_PUSH_PUBLIC_KEY', ''),
        'private_key' => env('WEB_PUSH_PRIVATE_KEY', ''),
    ],
];
