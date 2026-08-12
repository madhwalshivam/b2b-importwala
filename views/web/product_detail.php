<?php
$title = htmlspecialchars($product['title']) . " | ImportWala Wholesale";
ob_start();
?>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:36px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:32px; margin-top:12px;">

  <!-- Left: Product Media Gallery -->
  <div>
    <div style="width:100%; aspect-ratio:1/1; background:#f9fafb; border-radius:8px; overflow:hidden; border:1px solid #e5e7eb; position:relative;">
      <img src="<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" style="width:100%; height:100%; object-fit:cover;" id="mainProductImg">
      <span class="product-badge-moq" style="top:16px; left:16px; padding:6px 12px; font-size:12px;">MOQ: <?= $product['moq'] ?> PCS</span>
    </div>
  </div>

  <!-- Right: Product Details & Wholesale Tiers -->
  <div>
    <span style="color:#6b7280; font-size:13px; font-weight:600; text-transform:uppercase;">SKU: <?= htmlspecialchars($product['sku']) ?></span>
    <h1 style="font-size:24px; font-weight:800; color:#111827; margin:8px 0 16px 0; line-height:1.3;"><?= htmlspecialchars($product['title']) ?></h1>

    <!-- Base Unit Price & MOQ -->
    <div style="display:flex; align-items:baseline; gap:12px; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid #f3f4f6;">
      <span style="font-size:32px; font-weight:900; color:#f05a29;">$<?= number_format($product['base_price'], 2) ?></span>
      <span style="font-size:14px; color:#6b7280;">/ piece (Base Price)</span>
      <span style="background:#fff2ed; color:#f05a29; font-weight:700; font-size:12px; padding:4px 10px; border-radius:4px; margin-left:auto;">
        Min. Order: <?= $product['moq'] ?> pcs
      </span>
    </div>

    <!-- Tiered Pricing Volume Discount Table -->
    <?php if (!empty($product['tiered_prices'])): ?>
      <div style="margin-bottom:24px;">
        <h4 style="font-size:14px; font-weight:800; color:#111827; margin-bottom:10px;">WHOLESALE VOLUME DISCOUNTS</h4>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap:10px;">
          <?php foreach ($product['tiered_prices'] as $tier): ?>
            <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; padding:10px; text-align:center;">
              <div style="font-size:12px; color:#6b7280; font-weight:600;">
                <?= $tier['min_qty'] ?><?= $tier['max_qty'] ? (' - ' . $tier['max_qty']) : '+' ?> pcs
              </div>
              <div style="font-size:16px; font-weight:800; color:#f05a29; margin-top:2px;">
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
        <label style="font-size:14px; font-weight:800; color:#111827; display:block; margin-bottom:10px;">Select Color / Style:</label>
        <div style="display:flex; flex-wrap:wrap; gap:10px;" id="variationContainer">
          <?php foreach ($product['variations'] as $index => $var): ?>
            <button type="button" class="variation-btn <?= $index === 0 ? 'selected' : '' ?>" data-id="<?= $var['id'] ?>" style="padding:10px 16px; border:1.5px solid <?= $index === 0 ? '#f05a29' : '#d1d5db' ?>; background:<?= $index === 0 ? '#fff2ed' : '#fff' ?>; color:<?= $index === 0 ? '#f05a29' : '#374151' ?>; font-weight:700; border-radius:6px; cursor:pointer; font-size:13px;">
              <?= htmlspecialchars($var['color_name'] ?? ('Style #' . $var['id'])) ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Quantity & Add to Cart -->
    <div style="display:flex; align-items:center; gap:16px; margin-top:28px;">
      <div style="display:flex; align-items:center; border:1px solid #d1d5db; border-radius:6px; overflow:hidden;">
        <button type="button" onclick="adjustQty(-1)" style="width:40px; height:44px; border:none; background:#f3f4f6; font-size:18px; font-weight:700; cursor:pointer;">-</button>
        <input type="number" id="qtyInput" value="<?= $product['moq'] ?>" min="<?= $product['moq'] ?>" style="width:60px; height:44px; border:none; text-align:center; font-size:15px; font-weight:700; outline:none;">
        <button type="button" onclick="adjustQty(1)" style="width:40px; height:44px; border:none; background:#f3f4f6; font-size:18px; font-weight:700; cursor:pointer;">+</button>
      </div>

      <button type="button" onclick="addToCart()" class="btn-primary-orange" style="flex:1; justify-content:center; padding:14px;">
        Add To Wholesale Cart
      </button>
    </div>

    <!-- Alert / Status -->
    <div id="addToCartMsg" style="margin-top:16px; font-size:14px; font-weight:600;"></div>

  </div>

</div>

<script>
  let selectedVariationId = <?= !empty($product['variations']) ? $product['variations'][0]['id'] : 0 ?>;
  const moq = <?= (int)$product['moq'] ?>;

  document.querySelectorAll('.variation-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.variation-btn').forEach(b => {
        b.style.borderColor = '#d1d5db';
        b.style.background = '#fff';
        b.style.color = '#374151';
      });
      this.style.borderColor = '#f05a29';
      this.style.background = '#fff2ed';
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

  async function addToCart() {
    const qty = parseInt(document.getElementById('qtyInput').value) || moq;
    const msgDiv = document.getElementById('addToCartMsg');
    msgDiv.innerHTML = '<span style="color:#6b7280;">Adding to cart...</span>';

    try {
      const res = await fetch('/api/cart/add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          product_id: <?= $product['id'] ?>,
          variation_id: selectedVariationId,
          quantity: qty
        })
      });
      const data = await res.json();
      if (data.success) {
        msgDiv.innerHTML = '<span style="color:#10b981;">✓ Successfully added to wholesale cart!</span>';
        updateHeaderCartCount();
      } else {
        msgDiv.innerHTML = `<span style="color:#ef4444;">Error: ${data.message}</span>`;
      }
    } catch(e) {
      msgDiv.innerHTML = '<span style="color:#ef4444;">Failed to add to cart.</span>';
    }
  }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
