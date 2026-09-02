<?php
$title = "Cancellation and Order Change Policy | ImportWale Wholesale";

$pageTitle = "Cancellation and Order Change Policy";
$badgeText = "Order Policy";
$badgeIcon = "cancel";
$lastUpdated = "January 15, 2026";
$currentSlug = "cancellation-policy";
$showBusinessAddress = true;

$sections = [
    [
        'id' => 'cancellation-requests',
        'number' => '01',
        'title' => 'Cancellation Requests',
        'content' => 'Any request to cancel or change an order should be submitted to Importwale as soon as possible through the official support or order communication channel.'
    ],
    [
        'id' => 'orders-in-progress',
        'number' => '02',
        'title' => 'Orders in Progress',
        'content' => 'Once procurement, production, packing or dispatch has started, cancellation or modification may not be possible. Any costs already committed in connection with the order may be considered in the applicable commercial resolution.'
    ],
    [
        'id' => 'special-orders',
        'number' => '03',
        'title' => 'Made-to-Order or Special Orders',
        'content' => 'Customised, made-to-order or specially sourced products may not be cancellable once sourcing, procurement or production has commenced, unless otherwise agreed in writing.'
    ],
    [
        'id' => 'cancellation-by-importwale',
        'number' => '04',
        'title' => 'Cancellation by Importwale',
        'content' => 'We may cancel or suspend an order where reasonably necessary, including due to payment failure, pricing or listing error, suspected fraud, unavailable stock or legal restrictions. Any applicable payment adjustment will be handled according to the circumstances and applicable law.'
    ]
];

ob_start();
require __DIR__ . '/partials/policy_template.php';
$content = ob_get_clean();

require __DIR__ . '/layout.php';
?>
