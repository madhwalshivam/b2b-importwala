<?php
$title = "Inquiry #" . htmlspecialchars($inquiry['inquiry_number']) . " | Admin Panel";
require __DIR__ . '/../layouts/header.php';

$statuses = ['New', 'In Progress', 'Contacted', 'Quotation Sent', 'Converted', 'Closed', 'Rejected'];
$statusColors = [
  'New' => 'background:#fee2e2; color:#ef4444;',
  'In Progress' => 'background:#fef3c7; color:#d97706;',
  'Contacted' => 'background:#e0f2fe; color:#0284c7;',
  'Quotation Sent' => 'background:#f3e8ff; color:#9333ea;',
  'Converted' => 'background:#dcfce7; color:#16a34a;',
  'Closed' => 'background:#f1f5f9; color:#475569;',
  'Rejected' => 'background:#f1f5f9; color:#94a3b8;',
];
$badgeStyle = $statusColors[$inquiry['status']] ?? 'background:#f1f5f9; color:#475569;';
?>

<div class="content-wrapper" style="padding: 24px; max-width: 1200px; margin:0 auto;">

  <!-- Breadcrumb & Top Action -->
  <div style="margin-bottom:16px;">
    <a href="<?= url('admin/inquiries') ?>" style="font-size:13px; font-weight:700; color:#f05a29; text-decoration:none;">
      &larr; Back to Inquiries List
    </a>
  </div>

  <!-- Header Card -->
  <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 10px rgba(0,0,0,0.02);">
    <div>
      <div style="display:flex; align-items:center; gap:12px; margin-bottom:4px;">
        <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0;">
          Inquiry: <?= htmlspecialchars($inquiry['inquiry_number']) ?>
        </h1>
        <span style="font-size:12px; font-weight:800; padding:4px 12px; border-radius:14px; <?= $badgeStyle ?>">
          <?= htmlspecialchars($inquiry['status']) ?>
        </span>
      </div>
      <p style="font-size:13px; color:#64748b; margin:0;">
        Received on <?= date('d F Y, h:i A', strtotime($inquiry['created_at'])) ?>
      </p>
    </div>

    <!-- Status Change Form -->
    <form action="<?= url('admin/inquiries/update-status/' . $inquiry['id']) ?>" method="POST" style="display:flex; align-items:center; gap:8px;">
      <label style="font-size:13px; font-weight:700; color:#475569;">Update Status:</label>
      <select name="status" onchange="this.form.submit()" style="padding:8px 14px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; font-weight:700; background:#fff; cursor:pointer;">
        <?php foreach ($statuses as $st): ?>
          <option value="<?= $st ?>" <?= $inquiry['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>

  <div style="display:grid; grid-template-columns: 1fr 2fr; gap:24px; align-items:start;">

    <!-- Left Column: Customer Details & Internal Notes -->
    <div>
      
      <!-- Customer Info Card -->
      <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:20px; margin-bottom:24px; box-shadow:0 2px 10px rgba(0,0,0,0.02);">
        <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin:0 0 16px 0; border-bottom:1px solid #f1f5f9; padding-bottom:10px;">
          Customer Information
        </h3>

        <div style="display:flex; flex-direction:column; gap:12px; font-size:13px;">
          <div>
            <span style="color:#64748b; display:block; font-size:11px; font-weight:700; text-transform:uppercase;">Full Name</span>
            <strong style="color:#0f172a; font-size:15px;"><?= htmlspecialchars($inquiry['customer_name']) ?></strong>
          </div>

          <div>
            <span style="color:#64748b; display:block; font-size:11px; font-weight:700; text-transform:uppercase;">Phone / WhatsApp</span>
            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $inquiry['phone']) ?>" target="_blank" style="color:#25D366; font-weight:800; text-decoration:none; display:inline-flex; align-items:center; gap:4px;">
              <?= htmlspecialchars($inquiry['phone']) ?> &#x2197;
            </a>
          </div>

          <?php if (!empty($inquiry['email'])): ?>
            <div>
              <span style="color:#64748b; display:block; font-size:11px; font-weight:700; text-transform:uppercase;">Email Address</span>
              <span style="color:#334155; font-weight:600;"><?= htmlspecialchars($inquiry['email']) ?></span>
            </div>
          <?php endif; ?>

          <?php if (!empty($inquiry['company_name'])): ?>
            <div>
              <span style="color:#64748b; display:block; font-size:11px; font-weight:700; text-transform:uppercase;">Company / Business Name</span>
              <span style="color:#0f172a; font-weight:700;"><?= htmlspecialchars($inquiry['company_name']) ?></span>
            </div>
          <?php endif; ?>

          <?php if (!empty($inquiry['business_type'])): ?>
            <div>
              <span style="color:#64748b; display:block; font-size:11px; font-weight:700; text-transform:uppercase;">Business Type</span>
              <span style="color:#334155; font-weight:600;"><?= htmlspecialchars($inquiry['business_type']) ?></span>
            </div>
          <?php endif; ?>

          <?php if (!empty($inquiry['city']) || !empty($inquiry['state'])): ?>
            <div>
              <span style="color:#64748b; display:block; font-size:11px; font-weight:700; text-transform:uppercase;">Location</span>
              <span style="color:#334155; font-weight:600;"><?= htmlspecialchars(trim($inquiry['city'] . ', ' . $inquiry['state'], ', ')) ?></span>
            </div>
          <?php endif; ?>

          <?php if (!empty($inquiry['gst_number'])): ?>
            <div>
              <span style="color:#64748b; display:block; font-size:11px; font-weight:700; text-transform:uppercase;">GST Number</span>
              <span style="color:#334155; font-weight:600; font-family:monospace;"><?= htmlspecialchars($inquiry['gst_number']) ?></span>
            </div>
          <?php endif; ?>

          <?php if (!empty($inquiry['delivery_timeline'])): ?>
            <div>
              <span style="color:#64748b; display:block; font-size:11px; font-weight:700; text-transform:uppercase;">Expected Delivery Timeline</span>
              <span style="color:#334155; font-weight:600;"><?= htmlspecialchars($inquiry['delivery_timeline']) ?></span>
            </div>
          <?php endif; ?>

          <?php if (!empty($inquiry['customer_message'])): ?>
            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px; margin-top:6px;">
              <span style="color:#64748b; display:block; font-size:11px; font-weight:700; text-transform:uppercase; margin-bottom:4px;">Customer Message / Requirement</span>
              <div style="color:#334155; line-height:1.5; font-size:13px;"><?= nl2br(htmlspecialchars($inquiry['customer_message'])) ?></div>
            </div>
          <?php endif; ?>

        </div>
      </div>

      <!-- Admin Internal Notes Box -->
      <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.02);">
        <h3 style="font-size:15px; font-weight:800; color:#0f172a; margin:0 0 12px 0;">Internal Admin Notes</h3>
        <form action="<?= url('admin/inquiries/update-notes/' . $inquiry['id']) ?>" method="POST">
          <textarea name="admin_notes" rows="4" placeholder="Add private staff notes, quotation details, or discussion logs..." style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; margin-bottom:10px; resize:vertical; outline:none;"><?= htmlspecialchars($inquiry['admin_notes'] ?? '') ?></textarea>
          <button type="submit" style="background:#0f172a; color:#fff; font-size:12px; font-weight:700; padding:8px 16px; border:none; border-radius:6px; cursor:pointer;">
            Save Notes
          </button>
        </form>
      </div>

    </div>

    <!-- Right Column: Requested Products Table -->
    <div>
      <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.02);">
        
        <div style="padding:16px 20px; border-bottom:1px solid #e2e8f0; background:#f8fafc; display:flex; justify-content:space-between; align-items:center;">
          <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin:0;">Requested Products List</h3>
          <div style="font-size:13px; font-weight:700; color:#f05a29;">
            <?= $inquiry['total_products'] ?> Products &bull; <?= $inquiry['total_quantity'] ?> Total Units
          </div>
        </div>

        <div style="overflow-x:auto;">
          <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
            <thead>
              <tr style="background:#f1f5f9; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0;">
                <th style="padding:12px 16px; width:70px;">Image</th>
                <th style="padding:12px 16px;">Product Details</th>
                <th style="padding:12px 16px; text-align:center;">Requested Quantity</th>
                <th style="padding:12px 16px; text-align:right;">Snapshot Price</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($inquiry['items'] as $item): ?>
                <?php
                  $imgSrc = !empty($item['product_image_snapshot']) ? asset($item['product_image_snapshot']) : asset('assets/images/placeholder.jpg');
                ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                  <td style="padding:12px 16px;">
                    <div style="width:54px; height:54px; border-radius:8px; overflow:hidden; border:1px solid #e2e8f0; background:#f8fafc;">
                      <img src="<?= htmlspecialchars($imgSrc) ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                  </td>
                  <td style="padding:12px 16px;">
                    <div style="font-weight:700; color:#0f172a; font-size:14px;">
                      <?php if (!empty($item['current_product_slug'])): ?>
                        <a href="<?= url('product/' . $item['current_product_slug']) ?>" target="_blank" style="color:#0f172a; text-decoration:none;">
                          <?= htmlspecialchars($item['product_name_snapshot']) ?> &#x2197;
                        </a>
                      <?php else: ?>
                        <?= htmlspecialchars($item['product_name_snapshot']) ?>
                      <?php endif; ?>
                    </div>
                    <div style="font-size:12px; color:#64748b; margin-top:2px;">
                      <span>SKU: <strong style="color:#475569;"><?= htmlspecialchars($item['sku_snapshot'] ?: 'N/A') ?></strong></span>
                      <?php if (!empty($item['variation_name'])): ?>
                        &bull; <span>Variation: <strong style="color:#475569;"><?= htmlspecialchars($item['variation_name']) ?></strong></span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td style="padding:12px 16px; text-align:center;">
                    <span style="font-size:16px; font-weight:800; color:#f05a29; background:#fff7ed; padding:4px 14px; border-radius:8px; border:1px solid #ffd8a8; display:inline-block;">
                      <?= $item['quantity'] ?> Units
                    </span>
                  </td>
                  <td style="padding:12px 16px; text-align:right; font-weight:700; color:#334155;">
                    <?= $item['price_snapshot'] > 0 ? ('$' . number_format($item['price_snapshot'], 2)) : 'N/A' ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Summary Footer -->
        <div style="padding:18px 20px; background:#f8fafc; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
          <div style="font-size:14px; font-weight:700; color:#475569;">
            Inquiry Summary Totals:
          </div>
          <div style="font-size:16px; font-weight:800; color:#0f172a;">
            Total Products: <span style="color:#0f172a;"><?= $inquiry['total_products'] ?></span> &bull; 
            Total Requested Quantity: <span style="color:#f05a29; font-size:18px;"><?= $inquiry['total_quantity'] ?> Units</span>
          </div>
        </div>

      </div>
    </div>

  </div>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
