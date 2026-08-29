<?php
$title = 'RFQ #' . $rfq['id'] . ' — ' . htmlspecialchars($rfq['product_name']) . ' | Admin Panel';
require __DIR__ . '/../layouts/header.php';

$statuses = ['New', 'Contacted', 'Quoted', 'Closed'];
$statusColors = [
    'New'       => '#f05a29',
    'Contacted' => '#3b82f6',
    'Quoted'    => '#8b5cf6',
    'Closed'    => '#94a3b8',
];
$currentColor = $statusColors[$rfq['status']] ?? '#94a3b8';
?>

<div class="content-wrapper" style="padding:24px; max-width:1100px; margin:0 auto;">

  <!-- Breadcrumb + Back -->
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; flex-wrap:wrap; gap:10px;">
    <div style="display:flex; align-items:center; gap:8px; font-size:13px; color:#64748b;">
      <a href="<?= url('admin/rfq') ?>" style="color:#f05a29; text-decoration:none; font-weight:600;">← RFQ Requests</a>
      <span>/</span>
      <span>RFQ #<?= $rfq['id'] ?></span>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
      <!-- WhatsApp -->
      <a href="https://wa.me/91<?= htmlspecialchars($rfq['phone']) ?>" target="_blank" rel="noopener"
         style="display:inline-flex; align-items:center; gap:8px; background:#22c55e; color:#fff; font-weight:700; font-size:13px; padding:9px 16px; border-radius:9px; text-decoration:none;">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
        WhatsApp Buyer
      </a>
    </div>
  </div>

  <!-- Top Info Bar -->
  <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:20px 24px; margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; box-shadow:0 1px 6px rgba(0,0,0,.04);">
    <div>
      <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px;">RFQ #<?= $rfq['id'] ?></div>
      <div style="font-size:20px; font-weight:800; color:#0f172a;"><?= htmlspecialchars($rfq['product_name']) ?></div>
      <div style="font-size:12px; color:#64748b; margin-top:4px;">Submitted on <?= date('d M Y \a\t h:i A', strtotime($rfq['created_at'])) ?></div>
    </div>
    <!-- Status Selector -->
    <div style="display:flex; align-items:center; gap:12px;">
      <label style="font-size:12px; font-weight:700; color:#475569;">Status:</label>
      <select id="statusSelect" onchange="updateStatus(this.value)"
              style="padding:8px 14px; border:2px solid <?= $currentColor ?>; border-radius:9px; font-size:13px; font-weight:700; color:<?= $currentColor ?>; background:#fff; cursor:pointer; outline:none;">
        <?php foreach ($statuses as $st): ?>
          <option value="<?= $st ?>" <?= $rfq['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
        <?php endforeach; ?>
      </select>
      <span id="statusSaveMsg" style="font-size:12px; color:#10b981; display:none; font-weight:700;">✓ Saved</span>
    </div>
  </div>

  <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

    <!-- Step 1: Product Details -->
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:22px; box-shadow:0 1px 6px rgba(0,0,0,.04);">
      <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid #f1f5f9;">
        <div style="width:28px; height:28px; background:#fff7ed; border-radius:50%; display:flex; align-items:center; justify-content:center;">
          <span style="font-size:13px; font-weight:800; color:#f05a29;">1</span>
        </div>
        <h3 style="font-size:14px; font-weight:800; color:#0f172a; margin:0;">Product Details</h3>
      </div>

      <?php $fields = [
        'Product Name'   => $rfq['product_name'],
        'Quantity'       => number_format($rfq['quantity']) . ' ' . $rfq['unit'],
        'Target Price'   => '₹' . number_format($rfq['target_price'], 2),
        'Overall Budget' => $rfq['overall_budget'],
        'Sourcing Purpose' => $rfq['sourcing_purpose'],
      ]; ?>
      <?php foreach ($fields as $label => $value): ?>
        <div style="margin-bottom:14px;">
          <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;"><?= $label ?></div>
          <div style="font-size:14px; font-weight:600; color:#1e293b;"><?= htmlspecialchars($value) ?></div>
        </div>
      <?php endforeach; ?>

      <?php if (!empty($rfq['specifications'])): ?>
        <div style="margin-bottom:14px;">
          <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Specifications</div>
          <div style="font-size:13px; color:#1e293b; background:#f8fafc; border-radius:8px; padding:10px; border:1px solid #e2e8f0; line-height:1.6;"><?= nl2br(htmlspecialchars($rfq['specifications'])) ?></div>
        </div>
      <?php endif; ?>

      <?php if (!empty($rfq['product_reference_link'])): ?>
        <div>
          <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Reference Link</div>
          <a href="<?= htmlspecialchars($rfq['product_reference_link']) ?>" target="_blank" rel="noopener"
             style="font-size:13px; color:#f05a29; word-break:break-all; font-weight:600;">
            <?= htmlspecialchars(substr($rfq['product_reference_link'], 0, 60)) ?>...
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:middle; margin-left:3px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
          </a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Step 2: Contact Details + Step 3: Business Details -->
    <div style="display:flex; flex-direction:column; gap:20px;">

      <!-- Contact Details -->
      <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:22px; box-shadow:0 1px 6px rgba(0,0,0,.04);">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid #f1f5f9;">
          <div style="width:28px; height:28px; background:#eff6ff; border-radius:50%; display:flex; align-items:center; justify-content:center;">
            <span style="font-size:13px; font-weight:800; color:#3b82f6;">2</span>
          </div>
          <h3 style="font-size:14px; font-weight:800; color:#0f172a; margin:0;">Contact Details</h3>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
          <div>
            <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Full Name</div>
            <div style="font-size:14px; font-weight:600; color:#1e293b;"><?= htmlspecialchars($rfq['full_name']) ?></div>
          </div>
          <div>
            <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">WhatsApp / Phone</div>
            <div style="font-size:14px; font-weight:700; color:#1e293b;">+91 <?= htmlspecialchars($rfq['phone']) ?></div>
          </div>
          <div>
            <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Email</div>
            <div style="font-size:13px; font-weight:600; color:#1e293b; word-break:break-all;"><?= htmlspecialchars($rfq['email']) ?></div>
          </div>
          <div>
            <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Pincode</div>
            <div style="font-size:14px; font-weight:600; color:#1e293b;"><?= htmlspecialchars($rfq['pincode']) ?></div>
          </div>
        </div>
      </div>

      <!-- Business Details -->
      <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:22px; box-shadow:0 1px 6px rgba(0,0,0,.04);">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid #f1f5f9;">
          <div style="width:28px; height:28px; background:#f5f3ff; border-radius:50%; display:flex; align-items:center; justify-content:center;">
            <span style="font-size:13px; font-weight:800; color:#8b5cf6;">3</span>
          </div>
          <h3 style="font-size:14px; font-weight:800; color:#0f172a; margin:0;">Business Details</h3>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
          <div>
            <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Business Type</div>
            <div style="font-size:14px; font-weight:600; color:#1e293b;"><?= htmlspecialchars($rfq['business_type']) ?></div>
          </div>
          <div>
            <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">GST Registered</div>
            <div style="display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:700;
                 background:<?= $rfq['has_gst'] ? '#dcfce7' : '#fee2e2' ?>; color:<?= $rfq['has_gst'] ? '#16a34a' : '#dc2626' ?>;">
              <?= $rfq['has_gst'] ? '✓ Yes' : '✗ No' ?>
            </div>
          </div>
        </div>
        <?php if (!empty($rfq['additional_comments'])): ?>
          <div style="margin-top:14px;">
            <div style="font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:3px;">Additional Comments</div>
            <div style="font-size:13px; color:#1e293b; background:#f8fafc; border-radius:8px; padding:10px; border:1px solid #e2e8f0; line-height:1.6;"><?= nl2br(htmlspecialchars($rfq['additional_comments'])) ?></div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Reference Photos -->
  <?php if (!empty($rfq['photos'])): ?>
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:22px; margin-top:20px; box-shadow:0 1px 6px rgba(0,0,0,.04);">
      <h3 style="font-size:14px; font-weight:800; color:#0f172a; margin:0 0 16px 0;">
        Reference Photos (<?= count($rfq['photos']) ?>)
      </h3>
      <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <?php foreach ($rfq['photos'] as $photo): ?>
          <a href="<?= url($photo['file_path']) ?>" target="_blank" rel="noopener"
             style="display:block; width:110px; height:110px; border-radius:10px; overflow:hidden; border:2px solid #e2e8f0; flex-shrink:0; transition:border-color .2s;"
             onmouseover="this.style.borderColor='#f05a29'" onmouseout="this.style.borderColor='#e2e8f0'">
            <img src="<?= url($photo['file_path']) ?>" alt="<?= htmlspecialchars($photo['original_name'] ?? 'Photo') ?>"
                 style="width:100%; height:100%; object-fit:cover;" loading="lazy" />
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

</div>

<script>
async function updateStatus(newStatus) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const msg = document.getElementById('statusSaveMsg');
  const sel = document.getElementById('statusSelect');
  const statusColors = { 'New':'#f05a29','Contacted':'#3b82f6','Quoted':'#8b5cf6','Closed':'#94a3b8' };

  try {
    const res  = await fetch('<?= url('admin/rfq/update-status/' . $rfq['id']) ?>', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({ status: newStatus, _csrf_token: csrfToken }),
    });
    const data = await res.json();
    if (data.success) {
      sel.style.borderColor = statusColors[newStatus] || '#94a3b8';
      sel.style.color       = statusColors[newStatus] || '#94a3b8';
      msg.style.display     = 'inline';
      setTimeout(() => msg.style.display = 'none', 2500);
    }
  } catch (e) {
    alert('Could not update status. Please try again.');
  }
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
