<?php
$title = 'RFQ Requests | Admin Panel';
require __DIR__ . '/../layouts/header.php';

$statuses = ['New', 'Contacted', 'Quoted', 'Closed'];
$statusColors = [
    'New'       => 'bg-orange-100 text-orange-700 border-orange-200',
    'Contacted' => 'bg-blue-100 text-blue-700 border-blue-200',
    'Quoted'    => 'bg-purple-100 text-purple-700 border-purple-200',
    'Closed'    => 'bg-gray-100 text-gray-600 border-gray-200',
];
?>

<div class="content-wrapper" style="padding:24px;">

  <!-- Page Header -->
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
      <h1 style="font-size:24px; font-weight:800; color:#0f172a; margin:0 0 4px 0;">
        RFQ Requests
        <?php if (!empty($newCount) && $newCount > 0): ?>
          <span style="display:inline-flex; align-items:center; background:#f05a29; color:#fff; font-size:12px; font-weight:700; padding:3px 10px; border-radius:20px; margin-left:8px; vertical-align:middle;"><?= $newCount ?> New</span>
        <?php endif; ?>
      </h1>
      <p style="font-size:13px; color:#64748b; margin:0;">Manage buyer sourcing requirements, follow up via WhatsApp, and track status.</p>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
      <!-- Export CSV -->
      <a href="<?= url('admin/rfq/export-csv?' . http_build_query(['search' => $search, 'status' => $status, 'date_from' => $dateFrom, 'date_to' => $dateTo])) ?>"
         style="display:inline-flex; align-items:center; gap:6px; background:#10b981; color:#fff; font-size:13px; font-weight:700; padding:9px 16px; border-radius:8px; text-decoration:none; transition:background .2s;"
         onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        Export CSV
      </a>
    </div>
  </div>

  <!-- Filters -->
  <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:18px; margin-bottom:24px; box-shadow:0 1px 6px rgba(0,0,0,.04);">
    <form action="<?= url('admin/rfq') ?>" method="GET"
          style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr auto; gap:12px; align-items:end;">
      <div>
        <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:4px; text-transform:uppercase; letter-spacing:.5px;">Search</label>
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>"
               placeholder="Name, phone, email, product..."
               style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; outline:none;" />
      </div>
      <div>
        <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:4px; text-transform:uppercase; letter-spacing:.5px;">Status</label>
        <select name="status" style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px; background:#fff;">
          <option value="">All Statuses</option>
          <?php foreach ($statuses as $st): ?>
            <option value="<?= $st ?>" <?= ($status ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:4px; text-transform:uppercase; letter-spacing:.5px;">From Date</label>
        <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom ?? '') ?>"
               style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;" />
      </div>
      <div>
        <label style="display:block; font-size:11px; font-weight:700; color:#475569; margin-bottom:4px; text-transform:uppercase; letter-spacing:.5px;">To Date</label>
        <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo ?? '') ?>"
               style="width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:13px;" />
      </div>
      <div style="display:flex; gap:8px;">
        <button type="submit" style="background:#f05a29; color:#fff; font-weight:700; padding:9px 20px; border:none; border-radius:8px; cursor:pointer; font-size:13px; white-space:nowrap;">
          Filter
        </button>
        <a href="<?= url('admin/rfq') ?>" style="background:#f1f5f9; color:#475569; font-weight:700; padding:9px 14px; border-radius:8px; font-size:13px; text-decoration:none; white-space:nowrap; display:inline-flex; align-items:center;">
          Reset
        </a>
      </div>
    </form>
  </div>

  <!-- Table -->
  <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; box-shadow:0 1px 6px rgba(0,0,0,.04);">
    <div style="padding:14px 20px; border-bottom:1px solid #e2e8f0; background:#f8fafc; display:flex; justify-content:space-between; align-items:center;">
      <span style="font-size:13px; font-weight:700; color:#0f172a;">Total: <?= number_format($total ?? 0) ?> RFQ<?= ($total ?? 0) != 1 ? 's' : '' ?></span>
    </div>

    <div style="overflow-x:auto;">
      <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead>
          <tr style="background:#f1f5f9; color:#475569; font-weight:700; border-bottom:1px solid #e2e8f0;">
            <th style="padding:11px 16px; text-align:left;">Date</th>
            <th style="padding:11px 16px; text-align:left;">Product</th>
            <th style="padding:11px 16px; text-align:center;">Qty / Budget</th>
            <th style="padding:11px 16px; text-align:left;">Buyer</th>
            <th style="padding:11px 16px; text-align:left;">Phone</th>
            <th style="padding:11px 16px; text-align:center;">Status</th>
            <th style="padding:11px 16px; text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($rfqs)): ?>
            <tr>
              <td colspan="7" style="padding:48px; text-align:center; color:#94a3b8;">
                <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 12px; display:block; color:#cbd5e1;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                No RFQ requests found.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($rfqs as $rfq): ?>
              <?php
                $sc = $statusColors[$rfq['status']] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                $rowBg = $rfq['status'] === 'New' ? 'background:#fff9f5;' : '';
              ?>
              <tr style="border-bottom:1px solid #f1f5f9; transition:background .15s; <?= $rowBg ?>"
                  onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='<?= $rfq['status']==='New' ? '#fff9f5' : '#fff' ?>'">
                <td style="padding:12px 16px; color:#64748b; white-space:nowrap;">
                  <?= date('d M Y', strtotime($rfq['created_at'])) ?><br>
                  <span style="font-size:11px;"><?= date('h:i A', strtotime($rfq['created_at'])) ?></span>
                </td>
                <td style="padding:12px 16px; max-width:200px;">
                  <div style="font-weight:700; color:#0f172a; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="<?= htmlspecialchars($rfq['product_name']) ?>">
                    <?= htmlspecialchars($rfq['product_name']) ?>
                  </div>
                  <div style="font-size:11px; color:#94a3b8; margin-top:2px;"><?= htmlspecialchars($rfq['sourcing_purpose']) ?></div>
                </td>
                <td style="padding:12px 16px; text-align:center;">
                  <div style="font-weight:700; color:#0f172a;"><?= number_format($rfq['quantity']) ?> <?= htmlspecialchars($rfq['unit']) ?></div>
                  <div style="font-size:11px; color:#94a3b8;"><?= htmlspecialchars($rfq['overall_budget']) ?></div>
                </td>
                <td style="padding:12px 16px;">
                  <div style="font-weight:600; color:#1e293b;"><?= htmlspecialchars($rfq['full_name']) ?></div>
                  <div style="font-size:11px; color:#94a3b8;"><?= htmlspecialchars($rfq['business_type']) ?></div>
                </td>
                <td style="padding:12px 16px; white-space:nowrap;">
                  <span style="color:#1e293b;">+91 <?= htmlspecialchars($rfq['phone']) ?></span><br>
                  <span style="font-size:11px; color:#94a3b8;"><?= htmlspecialchars($rfq['email']) ?></span>
                </td>
                <td style="padding:12px 16px; text-align:center;">
                  <span class="<?= $sc ?>" style="display:inline-block; padding:3px 10px; border-radius:20px; border:1px solid; font-size:11px; font-weight:700; white-space:nowrap;">
                    <?= $rfq['status'] ?>
                  </span>
                </td>
                <td style="padding:12px 16px; text-align:right; white-space:nowrap;">
                  <!-- WhatsApp Quick-Action -->
                  <a href="https://wa.me/91<?= htmlspecialchars($rfq['phone']) ?>" target="_blank" rel="noopener"
                     title="WhatsApp <?= htmlspecialchars($rfq['full_name']) ?>"
                     style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; background:#dcfce7; color:#16a34a; border-radius:8px; text-decoration:none; margin-right:6px; transition:background .15s;"
                     onmouseover="this.style.background='#bbf7d0'" onmouseout="this.style.background='#dcfce7'">
                    <svg width="15" height="15" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                  </a>
                  <!-- View -->
                  <a href="<?= url('admin/rfq/' . $rfq['id']) ?>"
                     style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; background:#e0f2fe; color:#0369a1; border-radius:8px; text-decoration:none; margin-right:6px; transition:background .15s;"
                     onmouseover="this.style.background='#bae6fd'" onmouseout="this.style.background='#e0f2fe'" title="View">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </a>
                  <!-- Delete -->
                  <button onclick="deleteRfq(<?= $rfq['id'] ?>)"
                          style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; background:#fee2e2; color:#dc2626; border:none; border-radius:8px; cursor:pointer; transition:background .15s;"
                          onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'" title="Delete">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if (!empty($totalPages) && $totalPages > 1): ?>
      <div style="padding:14px 20px; border-top:1px solid #e2e8f0; display:flex; justify-content:center; gap:6px; flex-wrap:wrap;">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <a href="?<?= http_build_query(['search' => $search, 'status' => $status, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'page' => $p]) ?>"
             style="padding:6px 12px; border-radius:6px; font-size:13px; font-weight:600; text-decoration:none;
                    background:<?= $p == $currentPage ? '#f05a29' : '#f1f5f9' ?>;
                    color:<?= $p == $currentPage ? '#fff' : '#475569' ?>;">
            <?= $p ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
async function deleteRfq(id) {
  if (!confirm('Delete this RFQ request? This cannot be undone.')) return;
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
  try {
    const res  = await fetch('<?= url('admin/rfq/delete/') ?>' + id, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: '_csrf_token=' + encodeURIComponent(csrfToken),
    });
    const data = await res.json();
    if (data.success) {
      window.location.reload();
    } else {
      alert('Could not delete the RFQ. Please try again.');
    }
  } catch (e) {
    alert('Network error. Please try again.');
  }
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
