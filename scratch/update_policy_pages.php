<?php
$config = require __DIR__ . '/../config/app.php';
$GLOBALS['app_config'] = $config;
require_once __DIR__ . '/../app/Core/Database.php';

$db = App\Core\Database::getInstance();

$pages = [
    [
        'slug' => 'privacy-policy',
        'title' => 'Privacy Policy',
        'meta_title' => 'Privacy Policy | ImportWale Wholesale',
        'meta_description' => 'Privacy Policy for ImportWale B2B artificial jewellery wholesale platform.',
        'content' => '<h2>1. Introduction</h2><p>Importwale operates www.importwale.com as a business-to-business platform for artificial jewellery. This Privacy Policy explains how we collect, use, store and protect information provided by users of our website and services.</p><h2>2. Information We Collect</h2><p>We may collect information such as your name, business name, mobile number, email address, business or delivery address, account details, enquiry details, order information, transaction status and communications with us.</p><p>We may also collect limited technical information such as IP address, browser type, device information and website usage data for security, functionality and performance purposes.</p><h2>3. How We Use Information</h2><ul><li>To respond to enquiries and provide our services.</li><li>To create and manage business accounts and relationships.</li><li>To process quotations, orders, payments, delivery and customer support.</li><li>To improve website functionality and service quality.</li><li>To prevent fraud, misuse and unauthorised activity.</li><li>To comply with applicable legal, accounting, tax and regulatory requirements.</li></ul><h2>4. Sharing of Information</h2><p>We may share information with trusted service providers where necessary for payment processing, logistics, website operations, technology services, professional advice or legal compliance. We do not sell personal information merely for third-party advertising purposes.</p><h2>5. Data Security and Retention</h2><p>We use reasonable administrative, technical and organisational measures to protect information. Information is retained only for as long as reasonably necessary for legitimate business, legal, accounting, security or dispute-resolution purposes.</p><h2>6. Your Requests</h2><p>You may contact us using the official contact details available on www.importwale.com to request correction or updating of information. Certain information may need to be retained where required for legal, accounting, fraud-prevention or transaction-related purposes.</p><h2>7. Changes to This Policy</h2><p>We may update this Privacy Policy from time to time. The version published on www.importwale.com will be the current applicable version.</p>'
    ],
    [
        'slug' => 'terms-and-conditions',
        'title' => 'Terms and Conditions',
        'meta_title' => 'Terms and Conditions | ImportWale Wholesale',
        'meta_description' => 'Terms and Conditions for ImportWale B2B artificial jewellery platform.',
        'content' => '<h2>1. Acceptance of Terms</h2><p>By accessing or using www.importwale.com, you agree to these Terms and Conditions and any other policies referenced on the website.</p><h2>2. B2B Platform</h2><p>Importwale is intended for business and commercial transactions relating to artificial jewellery. Product listings, buyer enquiries, customer support and order-related communications are handled directly through Importwale. Users must provide accurate information and use the website only for lawful purposes.</p><h2>3. Product Information</h2><p>We make reasonable efforts to present product information accurately. However, colours, finishes, dimensions, weight and appearance may vary due to photography, screens, manufacturing processes and reasonable product variation. Final specifications and commercial terms applicable to an order will be those confirmed for that order.</p><h2>4. Pricing and Availability</h2><p>Prices, availability, minimum order quantities, taxes, shipping charges and other commercial terms may change without prior notice. A quotation or website display does not guarantee availability. Orders are subject to confirmation by Importwale.</p><h2>5. User Responsibilities</h2><p>Users must not provide false information, misuse the website, attempt unauthorised access, interfere with security or functionality, introduce malicious software, or infringe the rights of others.</p><h2>6. Intellectual Property</h2><p>The Importwale name, website content, product presentation, graphics, layouts and other protected materials may not be copied, reproduced or commercially exploited without prior permission, except where permitted by applicable law.</p><h2>7. Website Availability</h2><p>We may modify, update, suspend or discontinue any part of the website when reasonably required for maintenance, security, operational or business purposes.</p><h2>8. Governing Law</h2><p>These Terms are governed by applicable laws of India. Any dispute will be subject to applicable law and the jurisdiction of competent courts.</p>'
    ],
    [
        'slug' => 'shipping-policy',
        'title' => 'Shipping and Delivery Policy',
        'meta_title' => 'Shipping and Delivery Policy | ImportWale Wholesale',
        'meta_description' => 'Shipping and Delivery Policy for ImportWale B2B wholesale platform.',
        'content' => '<h2>1. Order Processing</h2><p>Order processing, dispatch and delivery timelines depend on product availability, order quantity, production requirements, destination and logistics arrangements.</p><h2>2. Delivery Estimates</h2><p>Any dispatch or delivery timeline communicated by Importwale is an estimate unless expressly confirmed as guaranteed in writing. Actual delivery may be affected by factors outside our reasonable control.</p><h2>3. Shipping Charges</h2><p>Applicable shipping, handling, packaging, insurance or other logistics charges, where applicable, will be communicated through the quotation, order confirmation or invoice.</p><h2>4. Delivery Information</h2><p>Buyers are responsible for providing complete and accurate delivery details, including the correct address, contact information and authorised recipient details.</p><h2>5. Delays</h2><p>Importwale will make reasonable efforts to facilitate timely delivery. We are not responsible for delays arising from carrier issues, weather, government action, incorrect buyer information or other circumstances outside reasonable control.</p><h2>6. Inspection on Delivery</h2><p>Buyers should inspect the shipment promptly after delivery and report visible damage, shortages or significant discrepancies through the official Importwale support channel with relevant evidence.</p><h2>7. Business Address</h2><p>Importwale<br>476 A1, Niti Khand-2, Ghaziabad, Uttar Pradesh 201014, India</p>'
    ],
    [
        'slug' => 'refund-policy',
        'title' => 'Return, Replacement and Refund Policy',
        'meta_title' => 'Return, Replacement and Refund Policy | ImportWale Wholesale',
        'meta_description' => 'Return, Replacement and Refund Policy for ImportWale B2B platform.',
        'content' => '<h2>1. B2B Transactions</h2><p>Transactions through Importwale are intended for business purposes. Returns are therefore not accepted for change of mind, unsold inventory, reduced demand or a change in buyer preference unless expressly agreed otherwise.</p><h2>2. Eligible Claims</h2><p>A claim may be considered where a verified issue exists, including a wrong product, material shortage, transit damage or significant mismatch from the confirmed order specification.</p><h2>3. Reporting an Issue</h2><p>The buyer should report the issue promptly through the official Importwale support channel and provide the relevant order reference and supporting evidence, such as clear photographs or videos where appropriate.</p><h2>4. Review and Resolution</h2><p>After reviewing the claim, Importwale may offer an appropriate resolution depending on the circumstances and confirmed order terms. A resolution may include replacement, credit, price adjustment or refund.</p><h2>5. Return Authorisation</h2><p>Products must not be returned without prior instructions or approval from Importwale. Unauthorised returns may not be accepted.</p><h2>6. Refund Processing</h2><p>Where a refund is approved, it will be processed through an appropriate payment method or commercial arrangement. Actual processing time may depend on banking or payment service providers.</p>'
    ],
    [
        'slug' => 'cancellation-policy',
        'title' => 'Cancellation and Order Change Policy',
        'meta_title' => 'Cancellation and Order Change Policy | ImportWale Wholesale',
        'meta_description' => 'Cancellation and Order Change Policy for ImportWale B2B platform.',
        'content' => '<h2>1. Cancellation Requests</h2><p>Any request to cancel or change an order should be submitted to Importwale as soon as possible through the official support or order communication channel.</p><h2>2. Orders in Progress</h2><p>Once procurement, production, packing or dispatch has started, cancellation or modification may not be possible. Any costs already committed in connection with the order may be considered in the applicable commercial resolution.</p><h2>3. Made-to-Order or Special Orders</h2><p>Customised, made-to-order or specially sourced products may not be cancellable once sourcing, procurement or production has commenced, unless otherwise agreed in writing.</p><h2>4. Cancellation by Importwale</h2><p>We may cancel or suspend an order where reasonably necessary, including due to payment failure, pricing or listing error, suspected fraud, unavailable stock or legal restrictions. Any applicable payment adjustment will be handled according to the circumstances and applicable law.</p>'
    ],
    [
        'slug' => 'payment-policy',
        'title' => 'Payment Policy',
        'meta_title' => 'Payment Policy | ImportWale Wholesale',
        'meta_description' => 'Payment Policy for ImportWale B2B wholesale platform.',
        'content' => '<h2>1. Payment Terms</h2><p>The payment terms applicable to an order, including advance payment, balance payment, taxes and other charges, will be communicated through the quotation, order confirmation, invoice or other official commercial communication.</p><h2>2. Payment Verification</h2><p>We may take reasonable steps to verify a payment or transaction where required for security, fraud prevention or order processing.</p><h2>3. Failed or Reversed Payments</h2><p>An order may be delayed, suspended or cancelled if payment fails, is reversed, is disputed or cannot reasonably be verified.</p><h2>4. Taxes</h2><p>Applicable taxes, including GST where relevant, will be charged and handled in accordance with applicable law and the invoicing arrangement.</p><h2>5. Payment Service Providers</h2><p>Payments may be processed using authorised third-party payment service providers. Users should provide payment information only through official and secure payment channels communicated by Importwale.</p><h2>6. Fraud Prevention</h2><p>We reserve the right to place a transaction on hold or request reasonable verification where there is a legitimate concern regarding fraud, unauthorised use or transaction security.</p>'
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
echo "Policy pages synced successfully!\n";
