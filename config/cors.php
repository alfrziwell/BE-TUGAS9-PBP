<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://alif-tugas9pbp.netlify.app/',    // Production website domain
        'https://apipbptugas9hph7fjiynxoqkzal7onbq3yehkzwzx41mvai2rmcs.soundofiwu.com/',    // API domain
    ],

    'allowed_origins_patterns' => [
        '/^http:\/\/localhost:\d+$/',
        '/^http:\/\/127\.0\.0\.1:\d+$/',
    ],
    'allowed_headers' => ['*'],

    'exposed_headers' => ['authorization', 'x-auth-token'],

    'max_age' => 0,

    'supports_credentials' => false,

];
