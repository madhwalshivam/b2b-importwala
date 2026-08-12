<?php
// Production Enterprise Database Configuration (Primary + Read Replicas support)

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'read' => [
                'host' => [
                    getenv('DB_READ_HOST') ?: '127.0.0.1',
                ],
            ],
            'write' => [
                'host' => [
                    getenv('DB_WRITE_HOST') ?: '127.0.0.1',
                ],
            ],
            'port'     => getenv('DB_PORT') ?: '3306',
            'dbname'   => getenv('DB_DATABASE') ?: 'ecommerce',
            'username' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
            'charset'  => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options'  => [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
            ],
        ],
    ],
];