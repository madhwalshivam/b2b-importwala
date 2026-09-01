<?php
$title = $pageTitle ?? "Order Cancellation Policy | ImportWale Wholesale";
ob_start();
?>

<div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 40px 32px; margin-bottom: 32px;">
  <span style="background: #fff4f0; color: #f05a29; font-size: 12px; font-weight: 700; text-transform: uppercase; padding: 4px 12px; border-radius: 20px; display: inline-block; margin-bottom: 12px;">
    B2B Order Management
  </span>
  <h1 style="font-size: 28px; font-weight: 800; color: #111827; margin-bottom: 10px;">Order Cancellation Policy</h1>
  <p style="font-size: 14px; color: #6b7280; margin: 0;">Guidelines for cancelling wholesale stock orders and custom production contracts.</p>
</div>

<div style="display: flex; flex-direction: column; gap: 24px; max-width: 960px; margin-bottom: 50px;">

  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px;">1. Standard In-Stock Order Cancellations</h3>
    <p style="font-size: 14px; color: #4b5563; line-height: 1.7;">
      Orders for standard catalog inventory can be cancelled <strong>prior to warehouse dispatch / courier tracking generation</strong> without any penalty fee. Once a shipment has been handed over to the air carrier or logistics provider, the order cannot be cancelled in transit.
    </p>
  </div>

  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px;">2. Custom OEM / RFQ Production Orders</h3>
    <p style="font-size: 14px; color: #4b5563; line-height: 1.7;">
      Custom manufactured, private-labeled, or custom-quoted bulk orders (RFQ) where raw materials or molds have been allocated can only be cancelled subject to raw material recovery costs outlined in your formal trade contract.
    </p>
  </div>

  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px;">3. Refund Processing Time</h3>
    <p style="font-size: 14px; color: #4b5563; line-height: 1.7;">
      Approved cancellations receive refunds processed back to the original payment source or added as instant account credit balance within <strong>3 to 5 business days</strong>.
    </p>
  </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
