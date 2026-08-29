<?php
$title = htmlspecialchars($product['name'] ?? $product['title'] ?? 'Product Detail') . " | ImportWala Wholesale";

$pImages        = get_product_images($product);
$totalImages    = count($pImages);
$mainImage      = $pImages[0] ?? asset('assets/images/placeholder.jpg');
$productName    = htmlspecialchars($product['name'] ?? $product['title'] ?? 'Wholesale Product');
$sku            = htmlspecialchars($product['sku'] ?? 'N/A');
$basePrice      = (float)($product['price'] ?? $product['base_price'] ?? 0);
$salePrice      = !empty($product['sale_price']) ? (float)$product['sale_price'] : null;
$effectivePrice = $salePrice ?: $basePrice;
$moq            = (int)($product['moq'] ?? 1);
$stock          = (int)($product['stock'] ?? 100);

$discountPct    = ($salePrice && $basePrice > $salePrice) 
    ? round((($basePrice - $salePrice) / $basePrice) * 100) 
    : 0;

$displayThumbs  = array_slice($pImages, 0, 4);
$hasMoreOverlay = ($totalImages > 4);
$remainingCount = $totalImages - 3;

// WhatsApp Business Enquiry Configuration
$settingModel = new \App\Models\Setting();
$whatsappNumber = preg_replace('/[^0-9]/', '', $settingModel->get('whatsapp_business_number') ?? '919217714452');
$singleTemplate = $settingModel->get('whatsapp_single_product_template') ?? "Hi, I want to enquire about this product:\n*Product:* {product_name}\n*SKU:* {sku}\n*URL:* {product_url}\n\nPlease share wholesale price & availability details.";
$productCanonicalUrl = url('product/' . ($product['slug'] ?? $product['id']));

$msg = str_replace(
    ['{product_name}', '{product_url}', '{sku}', '{price}'],
    [$productName, $productCanonicalUrl, $sku, '$' . number_format($effectivePrice, 2)],
    $singleTemplate
);

$singleProductWhatsappUrl = 'https://wa.me/' . $whatsappNumber . '?text=' . rawurlencode($msg);

ob_start();
?>

<!-- Breadcrumbs -->
<div style="margin: 12px 0 20px 0; font-size: 13px; color: #64748b;">
  <a href="<?= url('') ?>" style="color: #64748b; text-decoration: none;">Home</a> &nbsp;/&nbsp;
  <a href="<?= url('catalog') ?>" style="color: #64748b; text-decoration: none;">Catalog</a> &nbsp;/&nbsp;
  <?php if (!empty($product['category_name'])): ?>
    <a href="<?= url('catalog?category_id=' . ($product['category_id'] ?? '')) ?>" style="color: #64748b; text-decoration: none;"><?= htmlspecialchars($product['category_name']) ?></a> &nbsp;/&nbsp;
  <?php endif; ?>
  <span style="color: #0f172a; font-weight: 600;"><?= $productName ?></span>
</div>

<!-- Main Detail Card -->
<div style="display:grid; grid-template-columns: 460px 1fr; gap:40px; background:#fff; border:1px solid #f1f5f9; border-radius:24px; padding:32px; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.04);">

  <!-- Left: Product Media Gallery -->
  <div>
    <!-- Main Big Image -->
    <div style="width:100%; aspect-ratio:1/1; background:#f8fafc; border-radius:18px; overflow:hidden; border:1px solid #e2e8f0; position:relative; cursor:pointer;"
         onclick="openGlobalGalleryModal(<?= htmlspecialchars(json_encode($pImages, JSON_HEX_QUOT | JSON_HEX_TAG)) ?>, 0, '<?= $productName ?>')">
      <img src="<?= htmlspecialchars($mainImage) ?>" alt="<?= $productName ?>" style="width:100%; height:100%; object-fit:cover; transition: transform 0.3s ease;" id="mainDetailImg" onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>';">
      
      <!-- Top Badges -->
      <div style="position:absolute; top:14px; left:14px; display:flex; gap:8px; z-index:2;">
        <span class="pcard-badge-moq">MOQ: <?= $moq ?> PCS</span>
        <?php if ($discountPct > 0): ?>
          <span class="pcard-badge-tag tag-sale">-<?= $discountPct ?>% OFF</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Gallery Thumbnails Bar -->
    <?php if ($totalImages > 1): ?>
      <div style="display:flex; gap:10px; margin-top:14px; overflow-x:auto;">
        <?php foreach ($displayThumbs as $idx => $tUrl): 
          $isOverlay = ($hasMoreOverlay && $idx === 3);
        ?>
          <?php if ($isOverlay): ?>
            <button type="button" 
                    onclick="openGlobalGalleryModal(<?= htmlspecialchars(json_encode($pImages, JSON_HEX_QUOT | JSON_HEX_TAG)) ?>, 3, '<?= $productName ?>')"
                    style="width:80px; height:80px; border-radius:12px; border:2px solid #e2e8f0; overflow:hidden; position:relative; cursor:pointer; padding:0; flex-shrink:0; background:#0f172a;">
              <img src="<?= htmlspecialchars($tUrl) ?>" alt="" style="width:100%; height:100%; object-fit:cover; opacity:0.5;">
              <span style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#fff; font-size:16px; font-weight:800; font-family:system-ui, sans-serif;">+<?= $remainingCount ?></span>
            </button>
          <?php else: ?>
            <button type="button" 
                    onclick="document.getElementById('mainDetailImg').src = '<?= htmlspecialchars($tUrl) ?>'"
                    style="width:80px; height:80px; border-radius:12px; border:2px solid #e2e8f0; overflow:hidden; cursor:pointer; padding:0; flex-shrink:0; background:#f8fafc; transition:all 0.2s ease;">
              <img src="<?= htmlspecialchars($tUrl) ?>" alt="Thumb" style="width:100%; height:100%; object-fit:cover;" onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>';">
            </button>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Right: Product Information & Wholesale Pricing -->
  <div>
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
      <span style="color:#64748b; font-size:12px; font-weight:600; font-family:monospace;">SKU: <?= $sku ?></span>
      <?php if ($stock > 0): ?>
        <span style="background:#ecfdf5; color:#059669; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px;">IN STOCK (<?= $stock ?> pcs)</span>
      <?php else: ?>
        <span style="background:#fef2f2; color:#dc2626; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px;">OUT OF STOCK</span>
      <?php endif; ?>
    </div>

    <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px; flex-wrap:wrap;">
      <?php if (!empty($product['is_new']) || !empty($product['is_new_arrival'])): ?>
        <span style="background: #f05a29; color: #ffffff; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; text-transform: uppercase;">NEW</span>
      <?php endif; ?>
      <span style="color: #64748b; font-size: 12px; font-weight: 600;">Minimum Order Quantity: <?= $moq ?> Units</span>
      <?php 
        $tSold = (int)($product['total_sold'] ?? $product['sales_count'] ?? 0);
        if ($tSold > 0): 
      ?>
        <span style="color: #059669; font-size: 12px; font-weight: 700;">&bull; <?= number_format($tSold) ?>+ Sold</span>
      <?php endif; ?>
    </div>

    <h1 style="font-family:'Inter', system-ui, sans-serif; font-size:26px; font-weight:800; color:#0f172a; margin:0 0 16px 0; line-height:1.35;"><?= $productName ?></h1>

    <!-- Price Header -->
    <div style="display:flex; align-items:baseline; gap:12px; margin-bottom:20px; padding-bottom:18px; border-bottom:1px solid #f1f5f9;">
      <span style="font-size:36px; font-weight:900; color:#f05a29; font-family:'Inter', system-ui, sans-serif;">$<?= number_format($effectivePrice, 2) ?></span>
      <?php if ($salePrice && $basePrice > $salePrice): ?>
        <span style="font-size:16px; color:#94a3b8; text-decoration:line-through; font-weight:500;">$<?= number_format($basePrice, 2) ?></span>
      <?php endif; ?>
      <span style="font-size:14px; color:#64748b; font-weight:500;">/ piece (Base Wholesale Price)</span>
    </div>

    <!-- Tiered Pricing Volume Discount Cards -->
    <?php if (!empty($product['tiered_prices'])): ?>
      <div style="margin-bottom:24px; background:#fff7ed; border:1px solid #ffedd5; border-radius:16px; padding:16px;">
        <h4 style="font-size:13px; font-weight:800; color:#c2410c; margin:0 0 12px 0; letter-spacing:0.04em; text-transform:uppercase;">⚡ WHOLESALE VOLUME DISCOUNTS</h4>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap:10px;">
          <?php foreach ($product['tiered_prices'] as $tier): ?>
            <div style="background:#ffffff; border:1px solid #fed7aa; border-radius:10px; padding:10px; text-align:center;">
              <div style="font-size:11px; color:#64748b; font-weight:600;">
                <?= $tier['min_qty'] ?><?= $tier['max_qty'] ? (' - ' . $tier['max_qty']) : '+' ?> pcs
              </div>
              <div style="font-size:16px; font-weight:800; color:#f05a29; margin-top:3px;">
                $<?= number_format($tier['unit_price'], 2) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Variations Selector -->
    <?php if (!empty($product['variations'])): ?>
      <div style="margin-bottom:24px;">
        <label style="font-size:13px; font-weight:700; color:#0f172a; display:block; margin-bottom:10px;">Select Color / Variation:</label>
        <div style="display:flex; flex-wrap:wrap; gap:10px;" id="variationContainer">
          <?php foreach ($product['variations'] as $index => $var): ?>
            <button type="button" class="variation-btn <?= $index === 0 ? 'selected' : '' ?>" data-id="<?= $var['id'] ?>" style="padding:10px 16px; border:1.5px solid <?= $index === 0 ? '#f05a29' : '#e2e8f0' ?>; background:<?= $index === 0 ? '#fff7ed' : '#fff' ?>; color:<?= $index === 0 ? '#f05a29' : '#334155' ?>; font-weight:700; border-radius:10px; cursor:pointer; font-size:13px; transition:all 0.2s ease;">
              <?= htmlspecialchars($var['color_name'] ?? ('Style #' . $var['id'])) ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Quantity, Cart & WhatsApp Enquiry -->
    <div style="display:flex; flex-direction:column; gap:12px; margin-top:24px;">
      <div style="display:flex; align-items:center; gap:16px;">
        <div style="display:flex; align-items:center; border:1.5px solid #e2e8f0; border-radius:12px; overflow:hidden; background:#f8fafc;">
          <button type="button" onclick="adjustQty(-1)" style="width:44px; height:48px; border:none; background:transparent; font-size:18px; font-weight:700; cursor:pointer; color:#0f172a;">-</button>
          <input type="number" id="qtyInput" value="<?= $moq ?>" min="<?= $moq ?>" style="width:64px; height:48px; border:none; text-align:center; font-size:16px; font-weight:700; outline:none; background:transparent;">
          <button type="button" onclick="adjustQty(1)" style="width:44px; height:48px; border:none; background:transparent; font-size:18px; font-weight:700; cursor:pointer; color:#0f172a;">+</button>
        </div>

        <button type="button" onclick="addToInquiry()" class="pcard-btn-action" style="flex:1; height:48px; display:flex; align-items:center; justify-content:center; font-size:14px;">
          Add to Inquiry List
        </button>
      </div>

      <!-- Per-Product WhatsApp Enquiry Button -->
      <a href="<?= htmlspecialchars($singleProductWhatsappUrl) ?>" target="_blank" rel="noopener noreferrer"
         style="display:flex; align-items:center; justify-content:center; gap:10px; height:48px; background:#25D366; color:#ffffff; font-weight:700; font-size:14px; border-radius:12px; text-decoration:none; box-shadow: 0 4px 14px rgba(37,211,102,0.25); transition:transform 0.2s ease;">
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
        Enquire on WhatsApp
      </a>
    </div>

    <!-- Alert / Status -->
    <div id="addToCartMsg" style="margin-top:14px; font-size:13px; font-weight:600;"></div>

  </div>

</div>

<!-- Product Specifications & Description Tabs -->
<?php if (!empty($product['description'])): ?>
<div style="background:#fff; border:1px solid #f1f5f9; border-radius:24px; padding:32px; margin-top:28px;">
  <h3 style="font-size:18px; font-weight:800; color:#0f172a; margin-bottom:16px;">Product Description & Details</h3>
  <div style="font-size:14px; color:#475569; line-height:1.7;">
    <?= nl2br(htmlspecialchars($product['description'])) ?>
  </div>
</div>
<?php endif; ?>

<script>
  let selectedVariationId = <?= !empty($product['variations']) ? $product['variations'][0]['id'] : 0 ?>;
  const moq = <?= $moq ?>;

  document.querySelectorAll('.variation-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.variation-btn').forEach(b => {
        b.style.borderColor = '#e2e8f0';
        b.style.background = '#fff';
        b.style.color = '#334155';
      });
      this.style.borderColor = '#f05a29';
      this.style.background = '#fff7ed';
      this.style.color = '#f05a29';
      selectedVariationId = this.getAttribute('data-id');
    });
  });

  function adjustQty(delta) {
    const input = document.getElementById('qtyInput');
    let val = parseInt(input.value) || moq;
    val += delta;
    if (val < moq) val = moq;
    input.value = val;
  }

  async function addToInquiry() {
    const qty = parseInt(document.getElementById('qtyInput').value) || moq;
    const msgDiv = document.getElementById('addToCartMsg');
    msgDiv.innerHTML = '<span style="color:#64748b;">Adding to inquiry list...</span>';

    try {
      const res = await fetch('<?= url('api/inquiry/add') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: <?= $product['id'] ?>, variation_id: selectedVariationId, quantity: qty })
      });
      const data = await res.json();
      if (data.success) {
        msgDiv.innerHTML = '<span style="color:#10b981;">✓ Added to Inquiry List! <a href="<?= url("inquiry") ?>" style="color:#f05a29; font-weight:700; text-decoration:underline;">View My Inquiry &rsaquo;</a></span>';
        if (typeof updateHeaderInquiryCount === 'function') updateHeaderInquiryCount();
      } else {
        msgDiv.innerHTML = `<span style="color:#ef4444;">${data.message || 'Could not add to inquiry.'}</span>`;
      }
    } catch(e) {
      msgDiv.innerHTML = '<span style="color:#ef4444;">Server error. Please try again.</span>';
    }
  }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';

