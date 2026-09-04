<?php
$imgs = [
    'main' => 'public/uploads/products/url_1788414411_dc08d6b4e496fa7b4758477a55e11779.jpg',
    'img1' => 'public/uploads/products/url_1788414413_a6a05a173673afa5214b8fea0e87d7ee.jpg',
    'img2' => 'public/uploads/products/url_1788414416_a79ec82dea261f744dd1b0b40ba9d8d1.jpg',
    'img3' => 'public/uploads/products/url_1788414418_294053d5d83ea64cbaf486bb8055ed2c.jpg',
    'img4' => 'public/uploads/products/url_1788414420_7912f8a549dfa6fdfd98a8c108465b00.jpg',
];

foreach ($imgs as $k => $p) {
    if (file_exists($p)) {
        list($w, $h) = getimagesize($p);
        echo "$k: $p ($w x $h)\n";
    } else {
        echo "$k: NOT FOUND ($p)\n";
    }
}
