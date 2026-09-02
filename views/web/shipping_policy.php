<?php
$title = "Shipping and Delivery Policy | ImportWale Wholesale";

$pageTitle = "Shipping and Delivery Policy";
$badgeText = "Logistics Policy";
$badgeIcon = "truck";
$lastUpdated = "January 15, 2026";
$currentSlug = "shipping-policy";
$showBusinessAddress = true;

$sections = [
    [
        'id' => 'order-processing',
        'number' => '01',
        'title' => 'Order Processing',
        'content' => 'Order processing, dispatch and delivery timelines depend on product availability, order quantity, production requirements, destination and logistics arrangements.'
    ],
    [
        'id' => 'delivery-estimates',
        'number' => '02',
        'title' => 'Delivery Estimates',
        'content' => 'Any dispatch or delivery timeline communicated by Importwale is an estimate unless expressly confirmed as guaranteed in writing. Actual delivery may be affected by factors outside our reasonable control.'
    ],
    [
        'id' => 'shipping-charges',
        'number' => '03',
        'title' => 'Shipping Charges',
        'content' => 'Applicable shipping, handling, packaging, insurance or other logistics charges, where applicable, will be communicated through the quotation, order confirmation or invoice.'
    ],
    [
        'id' => 'delivery-information',
        'number' => '04',
        'title' => 'Delivery Information',
        'content' => 'Buyers are responsible for providing complete and accurate delivery details, including the correct address, contact information and authorised recipient details.'
    ],
    [
        'id' => 'delays',
        'number' => '05',
        'title' => 'Delays',
        'content' => 'Importwale will make reasonable efforts to facilitate timely delivery. We are not responsible for delays arising from carrier issues, weather, government action, incorrect buyer information or other circumstances outside reasonable control.'
    ],
    [
        'id' => 'inspection-on-delivery',
        'number' => '06',
        'title' => 'Inspection on Delivery',
        'content' => 'Buyers should inspect the shipment promptly after delivery and report visible damage, shortages or significant discrepancies through the official Importwale support channel with relevant evidence.'
    ]
];

ob_start();
require __DIR__ . '/partials/policy_template.php';
$content = ob_get_clean();

require __DIR__ . '/layout.php';
?>
