<?php
$title = $pageTitle ?? "Refund & Replacement Policy | ImportWale Wholesale";
ob_start();
?>

<!-- Policy Header -->
<div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 40px 32px; margin-bottom: 32px;">
  <span style="background: #ecfdf5; color: #059669; font-size: 12px; font-weight: 700; text-transform: uppercase; padding: 4px 12px; border-radius: 20px; display: inline-block; margin-bottom: 12px;">
    7-Day Quality Inspection Guarantee
  </span>
  <h1 style="font-size: 28px; font-weight: 800; color: #111827; margin-bottom: 10px;">Refund & Replacement Policy</h1>
  <p style="font-size: 14px; color: #6b7280; margin: 0;">Our commitment to 100% quality assurance, damaged goods replacement, and store credit refunds for B2B buyers.</p>
</div>

<div style="display: flex; flex-direction: column; gap: 24px; max-width: 960px; margin-bottom: 50px;">

  <!-- Section 1 -->
  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px;">1. 7-Day Quality Inspection Window</h3>
    <p style="font-size: 14px; color: #4b5563; line-height: 1.7;">
      Upon receipt of your wholesale shipment, buyers have <strong>7 calendar days</strong> to inspect product quality, quantities, and specifications. Any quality claims, missing items, or transit damages reported within this window are fully covered under our Replacement & Refund guarantee.
    </p>
  </div>

  <!-- Section 2 -->
  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px;">2. Eligible Replacement Claims</h3>
    <ul style="font-size: 14px; color: #4b5563; line-height: 1.8; margin: 0; padding-left: 20px;">
      <li>Goods physically damaged or broken during transit.</li>
      <li>Manufacturing defects or functional failure upon unboxing.</li>
      <li>Incorrect item or wrong color/variant delivered vs order confirmation.</li>
      <li>Quantity shortage vs packing slip invoice.</li>
    </ul>
  </div>

  <!-- Section 3 -->
  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px;">3. How to Submit a Claim</h3>
    <ol style="font-size: 14px; color: #4b5563; line-height: 1.8; margin: 0; padding-left: 20px;">
      <li>Navigate to our <a href="<?= url('contact-us') ?>" style="color:#f05a29; font-weight:700;">Contact Support page</a> or email <a href="mailto:support@importwale.com" style="color:#f05a29; font-weight:700;">support@importwale.com</a>.</li>
      <li>Provide your Order ID and photo/video proof showing the defective or damaged unit.</li>
      <li>Our Quality Assurance team will review the claim within <strong>24 business hours</strong>.</li>
      <li>Approved claims receive instant replacement dispatches or store credit refunds to your account balance.</li>
    </ol>
  </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
