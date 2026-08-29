<?php
$title = "Manage Customer Inquiries | Admin Panel";
require __DIR__ . '/../layouts/header.php';

$statuses = ['New', 'In Progress', 'Contacted', 'Quotation Sent', 'Converted', 'Closed', 'Rejected'];
$businessTypes = ['Distributor', 'Wholesaler', 'Retailer', 'E-commerce Seller', 'Manufacturer', 'Other'];
?>

<div class="content-wrapper" style="padding: 24px;">

  <!-- Header & Page Title -->
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
    <div>
      <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin: 0 0 4px 0;">Customer B2B Inquiries</h1>
      <p style="font-size: 13px; color: #64748b; margin: 0;">Manage multi-product wholesale requirements, quotations, and customer follow-ups.</p>
    </div>
  </div>

  <!-- Filters & Search Bar -->
  <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:18px; margin-bottom:24px; box-shadow:0 2px 10px rgba(0,0,0,0.02);">
    <form action="<?= url('admin/inquiries') ?>" method="GET" style="display:grid; grid-template-columns: 2fr 1fr 1fr 1fr 100px; gap:12px; align-items:end;">
      
      <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px;">Search Inquiries</label>
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search ID, Customer, Phone, Email, Product, SKU..." style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
      </div>

      <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px;">Status Filter</label>
        <select name="status" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; background:#fff;">
          <option value="">All Statuses</option>
          <?php foreach ($statuses as $st): ?>
            <option value="<?= $st ?>" <?= ($status ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px;">Business Type</label>
        <select name="business_type" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; background:#fff;">
          <option value="">All Types</option>
          <?php foreach ($businessTypes as $bt): ?>
            <option value="<?= $bt ?>" <?= ($businessType ?? '') === $bt ? 'selected' : '' ?>><?= $bt ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px;">Date From</label>
        <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom ?? '') ?>" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;">
      </div>

      <div>
        <button type="submit" style="width:100%; background:#f05a29; color:#fff; font-weight:700; padding:10px; border:none; border-radius:8px; cursor:pointer; font-size:13px;">
          Filter
        </button>
      </div>

    </form>
  </div>

  <!-- Inquiries Table -->
  <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.02);">
    
    <div style="padding:16px 20px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
      <div style="font-size:14px; font-weight:800; color:#0f172a;">
        Total Inquiries Found: <?= $total ?? 0 ?>
      </div>
      <?php if (!empty($newCount) && $newCount > 0): ?>
        <span style="background:#ef4444; color:#fff; font-size:12px; font-weight:800; padding:4px 10px; border-radius:20px;">
          <?= $newCount ?> New Inquiries Pending
        </span>
      <?php endif; ?>
    </div>

    <div style="overflow-x:auto;">
      <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
        <thead>
          <tr style="background:#f1f5f9; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0;">
            <th style="padding:12px 16px;">Inquiry ID</th>
            <th style="padding:12px 16px;">Customer / Company</th>
            <th style="padding:12px 16px;">Contact Info</th>
            <th style="padding:12px 16px; text-align:center;">Products</th>
            <th style="padding:12px 16px; text-align:center;">Total Qty</th>
            <th style="padding:12px 16px;">Status</th>
            <th style="padding:12px 16px;">Date</th>
            <th style="padding:12px 16px; text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($inquiries)): ?>
            <tr>
              <td colspan="8" style="padding:40px; text-align:center; color:#64748b;">
                No customer inquiries found.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($inquiries as $inq): ?>
              <?php
                $statusColors = [
                  'New' => 'background:#fee2e2; color:#ef4444;',
                  'In Progress' => 'background:#fef3c7; color:#d97706;',
                  'Contacted' => 'background:#e0f2fe; color:#0284c7;',
                  'Quotation Sent' => 'background:#f3e8ff; color:#9333ea;',
                  'Converted' => 'background:#dcfce7; color:#16a34a;',
                  'Closed' => 'background:#f1f5f9; color:#475569;',
                  'Rejected' => 'background:#f1f5f9; color:#94a3b8;',
                ];
                $badgeStyle = $statusColors[$inq['status']] ?? 'background:#f1f5f9; color:#475569;';
              ?>
              <tr style="border-bottom:1px solid #f1f5f9; transition:background 0.2s ease;">
                <td style="padding:12px 16px; font-weight:800; color:#f05a29;">
                  <a href="<?= url('admin/inquiries/' . $inq['id']) ?>" style="color:#f05a29; text-decoration:none;">
                    <?= htmlspecialchars($inq['inquiry_number']) ?>
                  </a>
                </td>
                <td style="padding:12px 16px;">
                  <div style="font-weight:700; color:#0f172a;"><?= htmlspecialchars($inq['customer_name']) ?></div>
                  <?php if (!empty($inq['company_name'])): ?>
                    <div style="font-size:11px; color:#64748b;"><?= htmlspecialchars($inq['company_name']) ?> (<?= htmlspecialchars($inq['business_type'] ?: 'Business') ?>)</div>
                  <?php endif; ?>
                </td>
                <td style="padding:12px 16px;">
                  <div style="font-weight:600; color:#334155;"><?= htmlspecialchars($inq['phone']) ?></div>
                  <?php if (!empty($inq['email'])): ?>
                    <div style="font-size:11px; color:#64748b;"><?= htmlspecialchars($inq['email']) ?></div>
                  <?php endif; ?>
                </td>
                <td style="padding:12px 16px; text-align:center; font-weight:700; color:#0f172a;">
                  <?= $inq['total_products'] ?> Products
                </td>
                <td style="padding:12px 16px; text-align:center; font-weight:800; color:#f05a29;">
                  <?= $inq['total_quantity'] ?> Units
                </td>
                <td style="padding:12px 16px;">
                  <span style="display:inline-block; font-size:11px; font-weight:800; padding:4px 10px; border-radius:12px; <?= $badgeStyle ?>">
                    <?= htmlspecialchars($inq['status']) ?>
                  </span>
                </td>
                <td style="padding:12px 16px; color:#64748b; font-size:12px;">
                  <?= date('d M Y, h:i A', strtotime($inq['created_at'])) ?>
                </td>
                <td style="padding:12px 16px; text-align:right;">
                  <a href="<?= url('admin/inquiries/' . $inq['id']) ?>" style="display:inline-block; background:#0f172a; color:#fff; font-size:12px; font-weight:700; padding:6px 12px; border-radius:6px; text-decoration:none;">
                    View &rsaquo;
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if (($totalPages ?? 1) > 1): ?>
      <div style="padding:16px 20px; border-top:1px solid #e2e8f0; display:flex; justify-content:center; gap:6px;">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <a href="<?= url('admin/inquiries?page=' . $p . '&search=' . urlencode($search) . '&status=' . urlencode($status)) ?>" 
             style="padding:6px 12px; border-radius:6px; font-size:12px; font-weight:700; text-decoration:none; <?= $p === $currentPage ? 'background:#f05a29; color:#fff;' : 'background:#f1f5f9; color:#475569;' ?>">
            <?= $p ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

  </div>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
