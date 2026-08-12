<?php
// Redis Cluster / Instance Configuration

return [
    'client' => 'predis', // or 'phpredis'
    'default' => [
        'host'     => getenv('REDIS_HOST') ?: '127.0.0.1',
        'port'     => (int)(getenv('REDIS_PORT') ?: 6379),
        'password' => getenv('REDIS_PASSWORD') ?: null,
        'database' => (int)(getenv('REDIS_DB') ?: 0),
        'timeout'  => 2.0,
    ],
    'cache' => [
        'host'     => getenv('REDIS_CACHE_HOST') ?: '127.0.0.1',
        'port'     => (int)(getenv('REDIS_CACHE_PORT') ?: 6379),
        'password' => getenv('REDIS_CACHE_PASSWORD') ?: null,
        'database' => (int)(getenv('REDIS_CACHE_DB') ?: 1),
        'timeout'  => 1.5,
    ],
    'session' => [
        'host'     => getenv('REDIS_SESSION_HOST') ?: '127.0.0.1',
        'port'     => (int)(getenv('REDIS_SESSION_PORT') ?: 6379),
        'password' => getenv('REDIS_SESSION_PASSWORD') ?: null,
        'database' => (int)(getenv('REDIS_SESSION_DB') ?: 2),
        'timeout'  => 1.5,
    ],
];
