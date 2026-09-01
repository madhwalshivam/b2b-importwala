<?php
$title = $pageTitle ?? "Terms & Conditions | ImportWale Wholesale";
ob_start();
?>

<div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 40px 32px; margin-bottom: 32px;">
  <span style="background: #f3f4f6; color: #374151; font-size: 12px; font-weight: 700; text-transform: uppercase; padding: 4px 12px; border-radius: 20px; display: inline-block; margin-bottom: 12px;">
    Legal Agreement
  </span>
  <h1 style="font-size: 28px; font-weight: 800; color: #111827; margin-bottom: 10px;">Terms & Conditions</h1>
  <p style="font-size: 14px; color: #6b7280; margin: 0;">Website usage, B2B wholesale transaction guidelines, and buyer agreements.</p>
</div>

<div style="display: flex; flex-direction: column; gap: 24px; max-width: 960px; margin-bottom: 50px;">

  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px;">1. Wholesale Buyer Accounts</h3>
    <p style="font-size: 14px; color: #4b5563; line-height: 1.7;">
      ImportWale is a dedicated B2B wholesale platform. By registering an account or placing an order, buyers represent that they are purchasing goods for commercial resale, trade, or business consumption.
    </p>
  </div>

  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px;">2. Pricing & Custom Quotations</h3>
    <p style="font-size: 14px; color: #4b5563; line-height: 1.7;">
      Catalog prices are shown exclusive or inclusive of applicable GST taxes as specified at checkout. Custom RFQ quotations are valid for the timeframe stated in the formal quote document.
    </p>
  </div>

  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px;">3. Governing Law & Jurisdiction</h3>
    <p style="font-size: 14px; color: #4b5563; line-height: 1.7;">
      All commercial transactions on ImportWale are governed by and construed in accordance with the laws of India. Courts at New Delhi, India shall have exclusive jurisdiction over any legal disputes.
    </p>
  </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
