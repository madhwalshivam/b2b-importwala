<?php
return [
    'host'       => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
    'username'   => getenv('SMTP_USER') ?: 'your-email@gmail.com',
    'password'   => getenv('SMTP_PASS') ?: 'your-app-password',
    'port'       => getenv('SMTP_PORT') ?: 587,
    'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
    'from_email' => getenv('SMTP_FROM_EMAIL') ?: 'no-reply@mudsor.com',
    'from_name'  => getenv('SMTP_FROM_NAME') ?: 'Mudsor Store'
];
