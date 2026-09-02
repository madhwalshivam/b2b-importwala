<?php
$title = "Privacy Policy | ImportWale Wholesale";

$pageTitle = "Privacy Policy";
$badgeText = "Data Privacy";
$badgeIcon = "shield";
$lastUpdated = "January 15, 2026";
$currentSlug = "privacy-policy";
$showBusinessAddress = true;

$sections = [
    [
        'id' => 'introduction',
        'number' => '01',
        'title' => 'Introduction',
        'content' => 'Importwale operates www.importwale.com as a business-to-business platform for artificial jewellery. This Privacy Policy explains how we collect, use, store and protect information provided by users of our website and services.'
    ],
    [
        'id' => 'information-we-collect',
        'number' => '02',
        'title' => 'Information We Collect',
        'content' => [
            'We may collect information such as your name, business name, mobile number, email address, business or delivery address, account details, enquiry details, order information, transaction status and communications with us.',
            'We may also collect limited technical information such as IP address, browser type, device information and website usage data for security, functionality and performance purposes.'
        ]
    ],
    [
        'id' => 'how-we-use-information',
        'number' => '03',
        'title' => 'How We Use Information',
        'content' => '<ul>
          <li>To respond to enquiries and provide our services.</li>
          <li>To create and manage business accounts and relationships.</li>
          <li>To process quotations, orders, payments, delivery and customer support.</li>
          <li>To improve website functionality and service quality.</li>
          <li>To prevent fraud, misuse and unauthorised activity.</li>
          <li>To comply with applicable legal, accounting, tax and regulatory requirements.</li>
        </ul>'
    ],
    [
        'id' => 'sharing-of-information',
        'number' => '04',
        'title' => 'Sharing of Information',
        'content' => 'We may share information with trusted service providers where necessary for payment processing, logistics, website operations, technology services, professional advice or legal compliance. We do not sell personal information merely for third-party advertising purposes.'
    ],
    [
        'id' => 'data-security-and-retention',
        'number' => '05',
        'title' => 'Data Security and Retention',
        'content' => 'We use reasonable administrative, technical and organisational measures to protect information. Information is retained only for as long as reasonably necessary for legitimate business, legal, accounting, security or dispute-resolution purposes.'
    ],
    [
        'id' => 'your-requests',
        'number' => '06',
        'title' => 'Your Requests',
        'content' => 'You may contact us using the official contact details available on www.importwale.com to request correction or updating of information. Certain information may need to be retained where required for legal, accounting, fraud-prevention or transaction-related purposes.'
    ],
    [
        'id' => 'changes-to-this-policy',
        'number' => '07',
        'title' => 'Changes to This Policy',
        'content' => 'We may update this Privacy Policy from time to time. The version published on www.importwale.com will be the current applicable version.'
    ]
];

ob_start();
require __DIR__ . '/partials/policy_template.php';
$content = ob_get_clean();

require __DIR__ . '/layout.php';
?>
