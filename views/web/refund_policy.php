<?php
$title = "Return, Replacement and Refund Policy | ImportWale Wholesale";

$pageTitle = "Return, Replacement and Refund Policy";
$badgeText = "Claims & Refunds";
$badgeIcon = "refresh";
$lastUpdated = "January 15, 2026";
$currentSlug = "refund-policy";
$showBusinessAddress = true;

$sections = [
    [
        'id' => 'b2b-transactions',
        'number' => '01',
        'title' => 'B2B Transactions',
        'content' => 'Transactions through Importwale are intended for business purposes. Returns are therefore not accepted for change of mind, unsold inventory, reduced demand or a change in buyer preference unless expressly agreed otherwise.'
    ],
    [
        'id' => 'eligible-claims',
        'number' => '02',
        'title' => 'Eligible Claims',
        'content' => 'A claim may be considered where a verified issue exists, including a wrong product, material shortage, transit damage or significant mismatch from the confirmed order specification.'
    ],
    [
        'id' => 'reporting-an-issue',
        'number' => '03',
        'title' => 'Reporting an Issue',
        'content' => 'The buyer should report the issue promptly through the official Importwale support channel and provide the relevant order reference and supporting evidence, such as clear photographs or videos where appropriate.'
    ],
    [
        'id' => 'review-and-resolution',
        'number' => '04',
        'title' => 'Review and Resolution',
        'content' => 'After reviewing the claim, Importwale may offer an appropriate resolution depending on the circumstances and confirmed order terms. A resolution may include replacement, credit, price adjustment or refund.'
    ],
    [
        'id' => 'return-authorisation',
        'number' => '05',
        'title' => 'Return Authorisation',
        'content' => 'Products must not be returned without prior instructions or approval from Importwale. Unauthorised returns may not be accepted.'
    ],
    [
        'id' => 'refund-processing',
        'number' => '06',
        'title' => 'Refund Processing',
        'content' => 'Where a refund is approved, it will be processed through an appropriate payment method or commercial arrangement. Actual processing time may depend on banking or payment service providers.'
    ]
];

ob_start();
require __DIR__ . '/partials/policy_template.php';
$content = ob_get_clean();

require __DIR__ . '/layout.php';
?>
