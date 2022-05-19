<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => false,

    'http' => [
        'server_key' => env('AAAA6rPuhHs:APA91bHqkn3tf1fiZ0iyOrpB9l88sngOsMMaR92UFXfoYgOWgMRS_oPHR2BGIf6ncgYh3qIg-RvxoHKIjJAX8_qSauYq9Z8gfu_wrr53nF9N7yGQES7l92ZzvznbeYhbhG7oqN0yjtEy', ''),
        'sender_id' => env('FCM_SENDER_ID', ''),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
];
