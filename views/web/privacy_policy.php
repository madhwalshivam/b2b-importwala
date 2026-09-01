<?php
$title = $pageTitle ?? "Privacy Policy | ImportWale Wholesale";
ob_start();
?>

<div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 40px 32px; margin-bottom: 32px;">
  <span style="background: #ecfdf5; color: #047857; font-size: 12px; font-weight: 700; text-transform: uppercase; padding: 4px 12px; border-radius: 20px; display: inline-block; margin-bottom: 12px;">
    Data Security & Privacy
  </span>
  <h1 style="font-size: 28px; font-weight: 800; color: #111827; margin-bottom: 10px;">Privacy Policy</h1>
  <p style="font-size: 14px; color: #6b7280; margin: 0;">How ImportWale collects, protects, and handles business buyer data.</p>
</div>

<div style="display: flex; flex-direction: column; gap: 24px; max-width: 960px; margin-bottom: 50px;">

  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px;">1. Information Collection & Usage</h3>
    <p style="font-size: 14px; color: #4b5563; line-height: 1.7;">
      We collect business contact information, GST numbers, shipping addresses, and transaction histories exclusively for fulfilling wholesale orders, customs documentation, and customer support.
    </p>
  </div>

  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px;">2. Payment & Data Security</h3>
    <p style="font-size: 14px; color: #4b5563; line-height: 1.7;">
      All financial transactions are processed over 256-bit SSL encrypted connections using certified payment gateways (Razorpay, Bank Wire transfer). ImportWale never stores full credit card numbers or banking credentials on public servers.
    </p>
  </div>

  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px;">3. Non-Disclosure Commitment</h3>
    <p style="font-size: 14px; color: #4b5563; line-height: 1.7;">
      We strictly respect trade secrecy and private labeling specifications. Buyer list data, custom RFQ designs, and sourcing volumes are never shared with unauthorized third parties.
    </p>
  </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
