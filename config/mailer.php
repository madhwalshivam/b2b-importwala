<?php
return [
    'host'       => env('MAIL_HOST'),
    'username'   => env('MAIL_USERNAME'),
    'password'   => env('MAIL_PASSWORD'),
    'port'       => env('MAIL_PORT', 587),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    'from_email' => env('MAIL_FROM_ADDRESS'),
    'from_name'  => env('MAIL_FROM_NAME')
];
