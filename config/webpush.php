<?php

return [
    'enabled' => env('WEB_PUSH_ENABLED', true),
    'vapid' => [
        'subject' => env('WEB_PUSH_SUBJECT', 'mailto:admin@presensi.com'),
        'public_key' => env('WEB_PUSH_PUBLIC_KEY', ''),
        'private_key' => env('WEB_PUSH_PRIVATE_KEY', ''),
    ],
];
