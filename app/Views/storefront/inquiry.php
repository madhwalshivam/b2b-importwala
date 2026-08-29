<?php
$title = "My Wholesale Inquiry | ImportWala";
ob_start();
?>

<div style="max-width: 1200px; margin: 0 auto; padding-top: 12px;">

  <!-- Page Header -->
  <div style="margin-bottom:24px; border-bottom:1px solid #f1f5f9; padding-bottom:16px;">
    <h1 style="font-family:'Inter', system-ui, sans-serif; font-size:26px; font-weight:800; color:#0f172a; margin:0 0 4px 0;">My Inquiry</h1>
    <p style="font-size:14px; color:#64748b; margin:0;">Review your selected wholesale products, adjust quantities per item, and submit your requirement for a direct quotation.</p>
  </div>

  <!-- Loading State -->
  <div id="inquiryLoading" style="text-align:center; padding:40px; color:#64748b;">
    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation:spin 1s linear infinite; margin-bottom:12px;">
      <circle cx="12" cy="12" r="10" stroke-width="2.5" stroke-dasharray="32" stroke-dashoffset="10"/>
    </svg>
    <div>Loading your inquiry list...</div>
  </div>

  <!-- Empty Inquiry List View (Hidden by default) -->
  <div id="inquiryEmpty" style="display:none; background:#fff; border:1px solid #f1f5f9; border-radius:24px; padding:60px 24px; text-align:center; box-shadow:0 4px 20px rgba(0,0,0,0.03);">
    <div style="width:72px; height:72px; background:#fff7ed; color:#f05a29; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px auto;">
      <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
    </div>
    <h3 style="font-size:20px; font-weight:800; color:#0f172a; margin:0 0 8px 0;">Your Inquiry List is Empty</h3>
    <p style="font-size:14px; color:#64748b; margin:0 0 20px 0;">Product cards ya detail page par "Add to Inquiry" click karke wholesale items list mein add karein.</p>
    <a href="<?= url('catalog') ?>" style="display:inline-block; background:#f05a29; color:#fff; font-weight:700; padding:12px 26px; border-radius:12px; text-decoration:none; font-size:14px; box-shadow:0 4px 14px rgba(240,90,41,0.25);">
      Browse Wholesale Catalog &rarr;
    </a>
  </div>

  <!-- Main Inquiry Layout Stage -->
  <div id="inquiryStage" style="display:none; grid-template-columns: 1.5fr 1fr; gap:28px; align-items:start;">
    
    <!-- Left Column: Items List -->
    <div>
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <h2 style="font-size:18px; font-weight:800; color:#0f172a; margin:0;">Selected Products (<span id="inquiryItemCountHeader">0</span>)</h2>
        <a href="<?= url('catalog') ?>" style="font-size:13px; font-weight:700; color:#f05a29; text-decoration:none;">+ Add More Products</a>
      </div>

      <div id="inquiryItemsContainer" style="display:flex; flex-direction:column; gap:16px;">
        <!-- Dynamic Item Cards rendered via JS -->
      </div>
    </div>

    <!-- Right Column: Form & Summary -->
    <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:20px; padding:24px; box-shadow:0 4px 20px rgba(0,0,0,0.04); position:sticky; top:90px;">
      
      <!-- Inquiry Summary Box -->
      <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:16px; margin-bottom:20px;">
        <h3 style="font-size:15px; font-weight:800; color:#0f172a; margin:0 0 12px 0; border-bottom:1px dashed #cbd5e1; padding-bottom:8px;">Inquiry Summary</h3>
        <div style="display:flex; justify-content:space-between; font-size:14px; color:#475569; margin-bottom:8px;">
          <span>Total Products:</span>
          <strong id="summaryTotalProducts" style="color:#0f172a;">0 Products</strong>
        </div>
        <div style="display:flex; justify-content:space-between; font-size:15px; color:#475569; border-top:1px solid #e2e8f0; padding-top:8px;">
          <span>Total Requested Quantity:</span>
          <strong id="summaryTotalQuantity" style="color:#f05a29; font-size:18px;">0 Units</strong>
        </div>
      </div>

      <!-- Customer Form -->
      <form id="inquiryForm" onsubmit="handleInquirySubmit(event)">
        <h3 style="font-size:16px; font-weight:800; color:#0f172a; margin:0 0 14px 0;">Customer Information</h3>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
          <div>
            <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px;">Full Name *</label>
            <input type="text" name="customer_name" id="inq_name" required placeholder="e.g. Shivam Sharma" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; outline:none;">
          </div>
          <div>
            <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px;">Mobile Number *</label>
            <input type="tel" name="phone" id="inq_phone" required placeholder="e.g. +91 9876543210" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; outline:none;">
          </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
          <div>
            <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px;">Email Address</label>
            <input type="email" name="email" id="inq_email" placeholder="name@business.com" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; outline:none;">
          </div>
          <div>
            <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px;">Company / Business Name</label>
            <input type="text" name="company_name" id="inq_company" placeholder="e.g. ABC Motors" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; outline:none;">
          </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
          <div>
            <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px;">City</label>
            <input type="text" name="city" id="inq_city" placeholder="Delhi / Mumbai" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; outline:none;">
          </div>
          <div>
            <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px;">State</label>
            <input type="text" name="state" id="inq_state" placeholder="State" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; outline:none;">
          </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
          <div>
            <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px;">GST Number (Optional)</label>
            <input type="text" name="gst_number" id="inq_gst" placeholder="07AAAAA0000A1Z5" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; outline:none;">
          </div>
          <div>
            <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px;">Business Type</label>
            <select name="business_type" id="inq_btype" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; outline:none; background:#fff;">
              <option value="Distributor">Distributor</option>
              <option value="Wholesaler">Wholesaler</option>
              <option value="Retailer">Retailer</option>
              <option value="E-commerce Seller">E-commerce Seller</option>
              <option value="Manufacturer">Manufacturer</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>

        <div style="margin-bottom:12px;">
          <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px;">Additional Requirements / Message</label>
          <textarea name="customer_message" id="inq_message" rows="2" placeholder="Specific colors, customization, or packaging details..." style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; outline:none; resize:vertical;"></textarea>
        </div>

        <div style="margin-bottom:16px;">
          <label style="display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:4px;">Expected Delivery Timeline</label>
          <input type="text" name="delivery_timeline" id="inq_timeline" placeholder="e.g. Immediate / 7-10 Days" style="width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px; font-size:13px; outline:none;">
        </div>

        <button type="submit" id="btnSubmitInquiry" style="width:100%; background:#f05a29; color:#ffffff; font-family:'Inter', sans-serif; font-size:15px; font-weight:800; padding:14px; border:none; border-radius:12px; cursor:pointer; box-shadow:0 4px 16px rgba(240,90,41,0.3); transition:all 0.2s ease;">
          Submit Inquiry Now &rarr;
        </button>

      </form>

    </div>

  </div>

</div>

<!-- Submission Success Modal -->
<div id="inquirySuccessModal" class="visual-search-modal-backdrop" style="display:none;">
  <div class="visual-search-modal-card" style="text-align:center; max-width:480px; padding:36px 28px;">
    <div style="width:68px; height:68px; border-radius:50%; background:#ecfdf5; color:#10b981; display:flex; align-items:center; justify-content:center; margin:0 auto 16px auto;">
      <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
      </svg>
    </div>

    <h2 style="font-size:22px; font-weight:800; color:#0f172a; margin:0 0 6px 0;">Inquiry Submitted Successfully</h2>
    <p style="font-size:13px; color:#64748b; margin:0 0 20px 0;">Our sales team has received your requirement and will contact you shortly with a wholesale quotation.</p>

    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:16px; margin-bottom:24px; text-align:left;">
      <div style="display:flex; justify-content:space-between; font-size:13px; margin-bottom:6px;">
        <span style="color:#64748b;">Inquiry Reference ID:</span>
        <strong style="color:#f05a29; font-size:14px;" id="successInquiryNum">INQ-00000</strong>
      </div>
      <div style="display:flex; justify-content:space-between; font-size:13px;">
        <span style="color:#64748b;">Summary:</span>
        <strong style="color:#0f172a;" id="successInquirySummary">0 Products · 0 Units</strong>
      </div>
    </div>

    <a href="<?= url('catalog') ?>" style="display:block; width:100%; background:#0f172a; color:#ffffff; font-weight:700; padding:12px; border-radius:10px; text-decoration:none; font-size:14px;">
      Continue Browsing Wholesale Catalog &rarr;
    </a>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  fetchInquiryList();
});

let currentInquiryItems = [];

async function fetchInquiryList() {
  const loading = document.getElementById('inquiryLoading');
  const empty = document.getElementById('inquiryEmpty');
  const stage = document.getElementById('inquiryStage');
  const container = document.getElementById('inquiryItemsContainer');

  try {
    const res = await fetch('<?= url("api/inquiry") ?>');
    const data = await res.json();
    currentInquiryItems = data.items || [];

    loading.style.display = 'none';

    if (currentInquiryItems.length === 0) {
      empty.style.display = 'block';
      stage.style.display = 'none';
      if (typeof updateHeaderInquiryCount === 'function') updateHeaderInquiryCount(0);
      return;
    }

    empty.style.display = 'none';
    stage.style.display = 'grid';

    document.getElementById('inquiryItemCountHeader').innerText = currentInquiryItems.length;
    document.getElementById('summaryTotalProducts').innerText = `${currentInquiryItems.length} Products`;
    document.getElementById('summaryTotalQuantity').innerText = `${data.total_quantity || 0} Units`;

    if (typeof updateHeaderInquiryCount === 'function') updateHeaderInquiryCount(currentInquiryItems.length);

    let html = '';
    currentInquiryItems.forEach((item, index) => {
      const priceFormatted = item.price > 0 ? `$${item.price.toFixed(2)}` : 'Contact for Price';
      html += `
        <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:16px; padding:16px; display:flex; gap:16px; align-items:center; box-shadow:0 2px 10px rgba(0,0,0,0.02);">
          <div style="width:80px; height:80px; border-radius:10px; overflow:hidden; border:1px solid #f1f5f9; flex-shrink:0; background:#f8fafc;">
            <img src="${item.image}" alt="${item.product_name}" style="width:100%; height:100%; object-fit:cover;">
          </div>
          <div style="flex:1;">
            <a href="<?= url('product/') ?>${item.slug}" style="font-size:15px; font-weight:700; color:#0f172a; text-decoration:none; line-height:1.3; display:block; margin-bottom:4px;">
              ${item.product_name}
            </a>
            <div style="font-size:12px; color:#64748b; margin-bottom:4px;">
              <span>SKU: <strong>${item.sku}</strong></span>
              ${item.variation_name ? ` &bull; <span>Variation: <strong>${item.variation_name}</strong></span>` : ''}
            </div>
            <div style="font-size:13px; font-weight:700; color:#f05a29;">${priceFormatted}</div>
          </div>
          
          <!-- Per-Product Quantity Control -->
          <div style="display:flex; align-items:center; gap:6px; background:#f8fafc; border:1px solid #cbd5e1; border-radius:10px; padding:4px 8px;">
            <button type="button" onclick="updateItemQuantity('${item.item_key}', ${item.quantity - 1})" style="width:24px; height:24px; border:none; background:#ffffff; border-radius:6px; font-weight:800; cursor:pointer; box-shadow:0 1px 3px rgba(0,0,0,0.1);">-</button>
            <input type="number" min="1" value="${item.quantity}" onchange="updateItemQuantity('${item.item_key}', parseInt(this.value) || 1)" style="width:48px; text-align:center; border:none; background:transparent; font-weight:800; font-size:14px; outline:none;">
            <button type="button" onclick="updateItemQuantity('${item.item_key}', ${item.quantity + 1})" style="width:24px; height:24px; border:none; background:#ffffff; border-radius:6px; font-weight:800; cursor:pointer; box-shadow:0 1px 3px rgba(0,0,0,0.1);">+</button>
          </div>

          <!-- Remove Item Action -->
          <button type="button" onclick="removeInquiryItem('${item.item_key}')" title="Remove Item" style="width:34px; height:34px; border:none; background:#fff1f2; color:#e11d48; border-radius:10px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s ease;">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          </button>
        </div>
      `;
    });

    container.innerHTML = html;

  } catch(err) {
    loading.style.display = 'none';
    alert('Failed to load inquiry items.');
  }
}

async function updateItemQuantity(itemKey, newQty) {
  try {
    const res = await fetch('<?= url("api/inquiry/update") ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ item_key: itemKey, quantity: newQty })
    });
    fetchInquiryList();
  } catch(e) {
    console.error(e);
  }
}

async function removeInquiryItem(itemKey) {
  if (!confirm('Remove this product from your inquiry list?')) return;
  try {
    const res = await fetch('<?= url("api/inquiry/remove") ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ item_key: itemKey })
    });
    fetchInquiryList();
  } catch(e) {
    console.error(e);
  }
}

async function handleInquirySubmit(e) {
  e.preventDefault();
  const btn = document.getElementById('btnSubmitInquiry');
  const originalText = btn.innerText;
  btn.disabled = true;
  btn.innerText = 'Submitting Inquiry...';

  const formData = {
    customer_name: document.getElementById('inq_name').value,
    phone: document.getElementById('inq_phone').value,
    email: document.getElementById('inq_email').value,
    company_name: document.getElementById('inq_company').value,
    city: document.getElementById('inq_city').value,
    state: document.getElementById('inq_state').value,
    gst_number: document.getElementById('inq_gst').value,
    business_type: document.getElementById('inq_btype').value,
    customer_message: document.getElementById('inq_message').value,
    delivery_timeline: document.getElementById('inq_timeline').value,
  };

  try {
    const res = await fetch('<?= url("api/inquiry/submit") ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    });
    const data = await res.json();
    btn.disabled = false;
    btn.innerText = originalText;

    if (data.success) {
      document.getElementById('successInquiryNum').innerText = data.inquiry_number;
      document.getElementById('successInquirySummary').innerText = `${data.total_products} Products · ${data.total_quantity} Units`;
      document.getElementById('inquirySuccessModal').style.display = 'flex';
      fetchInquiryList();
    } else {
      alert(data.message || 'Submission failed.');
    }
  } catch(err) {
    btn.disabled = false;
    btn.innerText = originalText;
    alert('Server error while submitting inquiry.');
  }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
