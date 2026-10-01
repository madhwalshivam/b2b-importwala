<?php
// Redis Cluster / Instance Configuration

return [
    'client' => 'predis', // or 'phpredis'
    'default' => [
        'host'     => env('REDIS_HOST'),
        'port'     => (int)env('REDIS_PORT', 6379),
        'password' => env('REDIS_PASSWORD'),
        'database' => (int)env('REDIS_DB', 0),
        'timeout'  => 2.0,
    ],
    'cache' => [
        'host'     => env('REDIS_CACHE_HOST', env('REDIS_HOST')),
        'port'     => (int)env('REDIS_CACHE_PORT', env('REDIS_PORT', 6379)),
        'password' => env('REDIS_CACHE_PASSWORD', env('REDIS_PASSWORD')),
        'database' => (int)env('REDIS_CACHE_DB', 1),
        'timeout'  => 1.5,
    ],
    'session' => [
        'host'     => env('REDIS_SESSION_HOST', env('REDIS_HOST')),
        'port'     => (int)env('REDIS_SESSION_PORT', env('REDIS_PORT', 6379)),
        'password' => env('REDIS_SESSION_PASSWORD', env('REDIS_PASSWORD')),
        'database' => (int)env('REDIS_SESSION_DB', 2),
        'timeout'  => 1.5,
    ],
];
