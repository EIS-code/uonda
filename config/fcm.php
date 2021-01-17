<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => false,

    'http' => [
        'server_key' => env('FCM_SERVER_KEY', 'AAAA_tqqDzs:APA91bGpRBXxw20bF31B55DgrJp1eTz5WzNTAZk3g2a2h9NuMkijU8XjUvjP3ckB93K_4jjSgSoC8zF0uzbBvHvBDfQSxupCAo6fB2O84bpUelCWbVW-7dprDeV2x6E3sgXJpPlx-J79'),
        'sender_id' => env('FCM_SENDER_ID', '1094590271291'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
];
