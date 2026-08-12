<?php
$urls = [
    'http://localhost/ecommerce/uploads/dfix/1785595009_6a6e0481eaa0f.png',
    'http://localhost/ecommerce/public/uploads/dfix/1785595009_6a6e0481eaa0f.png',
    'http://localhost/ecommerce/assets/images/placeholder.jpg',
    'http://localhost/ecommerce/public/assets/images/placeholder.jpg'
];

foreach ($urls as $url) {
    $headers = @get_headers($url);
    $status = $headers ? $headers[0] : 'FAILED';
    echo "URL: {$url}\n  STATUS: {$status}\n";
}
