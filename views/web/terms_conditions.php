<?php
$title = "Terms and Conditions | ImportWale Wholesale";

$pageTitle = "Terms and Conditions";
$badgeText = "Official Terms";
$badgeIcon = "doc";
$lastUpdated = "January 15, 2026";
$currentSlug = "terms-and-conditions";
$showBusinessAddress = true;

$sections = [
    [
        'id' => 'acceptance-of-terms',
        'number' => '01',
        'title' => 'Acceptance of Terms',
        'content' => 'By accessing or using www.importwale.com, you agree to these Terms and Conditions and any other policies referenced on the website.'
    ],
    [
        'id' => 'b2b-platform',
        'number' => '02',
        'title' => 'B2B Platform',
        'content' => 'Importwale is intended for business and commercial transactions relating to artificial jewellery. Product listings, buyer enquiries, customer support and order-related communications are handled directly through Importwale. Users must provide accurate information and use the website only for lawful purposes.'
    ],
    [
        'id' => 'product-information',
        'number' => '03',
        'title' => 'Product Information',
        'content' => 'We make reasonable efforts to present product information accurately. However, colours, finishes, dimensions, weight and appearance may vary due to photography, screens, manufacturing processes and reasonable product variation. Final specifications and commercial terms applicable to an order will be those confirmed for that order.'
    ],
    [
        'id' => 'pricing-and-availability',
        'number' => '04',
        'title' => 'Pricing and Availability',
        'content' => 'Prices, availability, minimum order quantities, taxes, shipping charges and other commercial terms may change without prior notice. A quotation or website display does not guarantee availability. Orders are subject to confirmation by Importwale.'
    ],
    [
        'id' => 'user-responsibilities',
        'number' => '05',
        'title' => 'User Responsibilities',
        'content' => 'Users must not provide false information, misuse the website, attempt unauthorised access, interfere with security or functionality, introduce malicious software, or infringe the rights of others.'
    ],
    [
        'id' => 'intellectual-property',
        'number' => '06',
        'title' => 'Intellectual Property',
        'content' => 'The Importwale name, website content, product presentation, graphics, layouts and other protected materials may not be copied, reproduced or commercially exploited without prior permission, except where permitted by applicable law.'
    ],
    [
        'id' => 'website-availability',
        'number' => '07',
        'title' => 'Website Availability',
        'content' => 'We may modify, update, suspend or discontinue any part of the website when reasonably required for maintenance, security, operational or business purposes.'
    ],
    [
        'id' => 'governing-law',
        'number' => '08',
        'title' => 'Governing Law',
        'content' => 'These Terms are governed by applicable laws of India. Any dispute will be subject to applicable law and the jurisdiction of competent courts.'
    ]
];

ob_start();
require __DIR__ . '/partials/policy_template.php';
$content = ob_get_clean();

require __DIR__ . '/layout.php';
?>
