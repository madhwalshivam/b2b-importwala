<?php
$title = $pageTitle ?? "Shipping & Air Freight Policy | ImportWale Wholesale";
ob_start();
?>

<!-- Policy Header -->
<div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 40px 32px; margin-bottom: 32px;">
  <span style="background: #eff6ff; color: #2563eb; font-size: 12px; font-weight: 700; text-transform: uppercase; padding: 4px 12px; border-radius: 20px; display: inline-block; margin-bottom: 12px;">
    B2B Logistics & Freight Terms
  </span>
  <h1 style="font-size: 28px; font-weight: 800; color: #111827; margin-bottom: 10px;">Shipping & Air Freight Policy</h1>
  <p style="font-size: 14px; color: #6b7280; margin: 0;">Comprehensive dispatch timelines, Free Air Shipping guidelines, and customs documentation standards for wholesale orders.</p>
</div>

<!-- Policy Content Cards -->
<div style="display: flex; flex-direction: column; gap: 24px; max-width: 960px; margin-bottom: 50px;">

  <!-- Card 1 -->
  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
      <span style="width: 32px; height: 32px; background: #fff4f0; color: #f05a29; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800;">1</span>
      Dispatch Timelines & Order Processing
    </h3>
    <div style="font-size: 14px; color: #4b5563; line-height: 1.7; space-y: 10px;">
      <p style="margin-bottom: 8px;">• <strong>Standard Wholesale In-Stock Items:</strong> Dispatched within 24 to 48 business hours from order verification.</p>
      <p style="margin-bottom: 8px;">• <strong>Custom RFQ & Bulk Factory Sourcing:</strong> Production lead time is specified in your custom quotation agreement (typically 5–12 business days).</p>
      <p style="margin: 0;">• Tracking numbers and courier dispatch links are updated automatically on your account dashboard and sent via email/SMS.</p>
    </div>
  </div>

  <!-- Card 2 -->
  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
      <span style="width: 32px; height: 32px; background: #eff6ff; color: #2563eb; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800;">2</span>
      Free Air Shipping & Shipping Modes
    </h3>
    <div style="font-size: 14px; color: #4b5563; line-height: 1.7;">
      <p style="margin-bottom: 8px;">• <strong>Free Air Shipping:</strong> Applicable on designated items bearing the "Free Air Shipping" badge across top product lines.</p>
      <p style="margin-bottom: 8px;">• <strong>Express Air Cargo:</strong> Fast air transport delivered via tier-1 global couriers (DHL, FedEx, Aramex, Express Surface). Transit time: 3 to 7 business days.</p>
      <p style="margin: 0;">• <strong>LCL / FCL Container Freight:</strong> Available for heavy bulk machinery, palletized goods, and container loads upon buyer request.</p>
    </div>
  </div>

  <!-- Card 3 -->
  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 28px;">
    <h3 style="font-size: 18px; font-weight: 800; color: #111827; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
      <span style="width: 32px; height: 32px; background: #ecfdf5; color: #059669; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 800;">3</span>
      Customs Duties, Taxes & Export Invoicing
    </h3>
    <div style="font-size: 14px; color: #4b5563; line-height: 1.7;">
      <p style="margin-bottom: 8px;">• <strong>Domestic Orders (India):</strong> GST Tax Invoices with Input Tax Credit (ITC) eligibility are generated automatically.</p>
      <p style="margin: 0;">• <strong>International Orders:</strong> Shipments include formal commercial invoices, packing slips, HS codes, and export declarations. Import tariffs or customs duties in destination countries are handled as per standard incoterms (DDP / DAP).</p>
    </div>
  </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
