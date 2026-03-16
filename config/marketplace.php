<?php

return [
    'content_disk' => env('MARKETPLACE_CONTENT_DISK', 'local'),
    'media_disk' => env('MARKETPLACE_MEDIA_DISK', 'public'),
    'currency' => env('MARKETPLACE_CURRENCY', 'JPY'),
    'payment_gateway' => env('MARKETPLACE_PAYMENT_GATEWAY', 'mock'),
    'webhook_secret' => env('MARKETPLACE_WEBHOOK_SECRET', 'mock-webhook-secret'),
    'seed_target_contents' => (int) env('MARKETPLACE_SEED_TARGET_CONTENTS', 18),
];
