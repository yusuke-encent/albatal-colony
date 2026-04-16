<?php

return [
    'content_disk' => env('MARKETPLACE_CONTENT_DISK', 'private'),
    'media_disk' => env('MARKETPLACE_MEDIA_DISK', 'public'),
    'currency' => env('MARKETPLACE_CURRENCY', 'JPY'),
    'default_provider_prices' => [480, 930, 1490, 1798, 2021, 3000, 3300, 4593, 9980, 12800],
    'payment_gateway' => env('MARKETPLACE_PAYMENT_GATEWAY', 'mock'),
    'webhook_secret' => env('MARKETPLACE_WEBHOOK_SECRET', 'mock-webhook-secret'),
    'seed_target_contents' => (int) env('MARKETPLACE_SEED_TARGET_CONTENTS', 18),
];
