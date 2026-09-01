<?php
$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();

$pages = [
    [
        'slug' => 'support',
        'title' => 'Help Center & FAQs',
        'meta_title' => 'Help Center & FAQs - ImportWale Wholesale Support',
        'meta_description' => 'Find answers to wholesale ordering, MOQ, express air shipping, GST invoicing, and return policies.',
        'content' => '<h2>Help Center & FAQs</h2><p>Welcome to the ImportWale Help Center. Find instant answers for B2B wholesale ordering, shipping, payments, and quality guarantees.</p>'
    ],
    [
        'slug' => 'contact-us',
        'title' => 'Contact Support',
        'meta_title' => 'Contact Support - ImportWale Wholesale',
        'meta_description' => 'Get in touch with ImportWale B2B customer support for order assistance, shipping queries, and custom quotes.',
        'content' => '<h2>Contact Support</h2><p>Email: support@importwale.com | Phone: +91 9217714452 | Hours: Mon-Sat 10 AM - 7 PM IST</p>'
    ],
    [
        'slug' => 'shipping-policy',
        'title' => 'Shipping & Air Freight Policy',
        'meta_title' => 'Shipping & Air Freight Policy - ImportWale',
        'meta_description' => 'Read ImportWale shipping lead times, Free Air Shipping terms, express air cargo, and customs clearance procedures.',
        'content' => '<h2>Shipping & Air Freight Policy</h2><p>In-stock wholesale orders dispatch within 24-48 hours. Express Air Freight transit takes 3 to 7 business days.</p>'
    ],
    [
        'slug' => 'refund-policy',
        'title' => 'Refund & Replacement Policy',
        'meta_title' => 'Refund & Replacement Policy - ImportWale',
        'meta_description' => 'ImportWale 7-day quality inspection guarantee, replacement process for transit damage, and store credit refunds.',
        'content' => '<h2>Refund & Replacement Policy</h2><p>7-Day quality inspection guarantee. Replacements or store credits are issued within 24-48 hours for damaged or defective items.</p>'
    ],
    [
        'slug' => 'cancellation-policy',
        'title' => 'Order Cancellation Policy',
        'meta_title' => 'Order Cancellation Policy - ImportWale',
        'meta_description' => 'Rules for cancelling wholesale stock orders prior to dispatch.',
        'content' => '<h2>Order Cancellation Policy</h2><p>In-stock orders can be cancelled prior to warehouse dispatch without penalty.</p>'
    ],
    [
        'slug' => 'terms-and-conditions',
        'title' => 'Terms & Conditions',
        'meta_title' => 'Terms & Conditions - ImportWale',
        'meta_description' => 'ImportWale wholesale terms of service and buyer agreements.',
        'content' => '<h2>Terms & Conditions</h2><p>Commercial B2B buyer terms and conditions for transactions on ImportWale.</p>'
    ],
    [
        'slug' => 'privacy-policy',
        'title' => 'Privacy Policy',
        'meta_title' => 'Privacy Policy - ImportWale',
        'meta_description' => 'ImportWale buyer data security and non-disclosure commitments.',
        'content' => '<h2>Privacy Policy</h2><p>We respect trade confidentiality and protect buyer data using 256-bit SSL encryption.</p>'
    ]
];

$stmtSelect = $db->prepare("SELECT id FROM cms_pages WHERE slug = ?");
$stmtInsert = $db->prepare("INSERT INTO cms_pages (title, slug, content, meta_title, meta_description) VALUES (?, ?, ?, ?, ?)");
$stmtUpdate = $db->prepare("UPDATE cms_pages SET title = ?, content = ?, meta_title = ?, meta_description = ? WHERE slug = ?");

foreach ($pages as $p) {
    $stmtSelect->execute([$p['slug']]);
    if ($stmtSelect->fetch()) {
        $stmtUpdate->execute([$p['title'], $p['content'], $p['meta_title'], $p['meta_description'], $p['slug']]);
        echo "Updated page: {$p['slug']}\n";
    } else {
        $stmtInsert->execute([$p['title'], $p['slug'], $p['content'], $p['meta_title'], $p['meta_description']]);
        echo "Inserted page: {$p['slug']}\n";
    }
}
echo "CMS pages sync completed successfully!\n";
