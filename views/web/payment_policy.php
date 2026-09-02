<?php
$title = "Payment Policy | ImportWale Wholesale";

$pageTitle = "Payment Policy";
$badgeText = "Payment Terms";
$badgeIcon = "credit-card";
$lastUpdated = "January 15, 2026";
$currentSlug = "payment-policy";
$showBusinessAddress = true;

$sections = [
    [
        'id' => 'payment-terms',
        'number' => '01',
        'title' => 'Payment Terms',
        'content' => 'The payment terms applicable to an order, including advance payment, balance payment, taxes and other charges, will be communicated through the quotation, order confirmation, invoice or other official commercial communication.'
    ],
    [
        'id' => 'payment-verification',
        'number' => '02',
        'title' => 'Payment Verification',
        'content' => 'We may take reasonable steps to verify a payment or transaction where required for security, fraud prevention or order processing.'
    ],
    [
        'id' => 'failed-or-reversed-payments',
        'number' => '03',
        'title' => 'Failed or Reversed Payments',
        'content' => 'An order may be delayed, suspended or cancelled if payment fails, is reversed, is disputed or cannot reasonably be verified.'
    ],
    [
        'id' => 'taxes',
        'number' => '04',
        'title' => 'Taxes & Compliance',
        'content' => 'Applicable taxes, including GST where relevant, will be charged and handled in accordance with applicable law and the invoicing arrangement.'
    ],
    [
        'id' => 'payment-service-providers',
        'number' => '05',
        'title' => 'Payment Service Providers',
        'content' => 'Payments may be processed using authorised third-party payment service providers. Users should provide payment information only through official and secure payment channels communicated by Importwale.'
    ],
    [
        'id' => 'fraud-prevention',
        'number' => '06',
        'title' => 'Fraud Prevention',
        'content' => 'We reserve the right to place a transaction on hold or request reasonable verification where there is a legitimate concern regarding fraud, unauthorised use or transaction security.'
    ]
];

ob_start();
require __DIR__ . '/partials/policy_template.php';
$content = ob_get_clean();

require __DIR__ . '/layout.php';
?>
