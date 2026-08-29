<?php
$title = "Wholesale Shopping Cart | ImportWala";
ob_start();
?>

<h1 style="font-size:28px; font-weight:900; color:#111827; margin-bottom:20px;">Wholesale Cart Summary</h1>

<div style="display:grid; grid-template-columns: 1fr 380px; gap:28px;" id="cartAppContainer">
  
  <!-- Left: Cart Items List -->
  <div style="background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:24px;">
    
    <!-- Free Shipping Progress Tracker -->
    <div style="background:#fff7f4; border:1px solid #ffd8c8; border-radius:8px; padding:16px; margin-bottom:24px;" id="freeShippingBanner">
      <div style="font-size:14px; font-weight:700; color:#f05a29;" id="shippingStatusText">
        Calculating shipping progress...
      </div>
      <div class="shipping-progress-bar">
        <div class="shipping-progress-fill" id="shippingProgressBar" style="width: 0%;"></div>
      </div>
    </div>

    <!-- Items List Container -->
    <div id="cartItemsList">
      <p style="text-align:center; color:#6b7280; padding:20px;">Loading your cart items...</p>
    </div>
  </div>

  <!-- Right: Order Summary Sidebar -->
  <div class="cart-summary-box">
    <h3 style="font-size:18px; font-weight:800; color:#111827; margin-bottom:16px; border-bottom:1px solid #f3f4f6; padding-bottom:12px;">Order Summary</h3>
    
    <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14px; color:#4b5563;">
      <span>Total Quantity:</span>
      <span id="summaryTotalItems" style="font-weight:700; color:#111827;">0 pcs</span>
    </div>

    <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14px; color:#4b5563;">
      <span>Estimated Weight:</span>
      <span id="summaryTotalWeight" style="font-weight:700; color:#111827;">0.00 kg</span>
    </div>

    <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:14px; color:#4b5563;">
      <span>Wholesale Subtotal:</span>
      <span id="summarySubtotal" style="font-weight:800; color:#f05a29; font-size:16px;">$0.00</span>
    </div>

    <div style="border-top:1px solid #e5e7eb; padding-top:16px; margin-top:16px;">
      <button type="button" onclick="triggerCheckout()" class="btn-primary-orange" style="width:100%; justify-content:center; padding:14px; font-size:16px;">
        Proceed To Checkout
      </button>
    </div>

    <div id="checkoutResultMsg" style="margin-top:16px; font-size:13px; text-align:center;"></div>
  </div>

</div>

<script>
  async function loadCartPage() {
    try {
      const res = await fetch('<?= url('api/cart') ?>');
      const data = await res.json();
      if (!data.success || !data.cart) return;

      const cart = data.cart;
      document.getElementById('summaryTotalItems').innerText = (cart.items_count || 0) + ' pcs';
      document.getElementById('summaryTotalWeight').innerText = (cart.total_weight_kg || 0) + ' kg';
      document.getElementById('summarySubtotal').innerText = '$' + (cart.subtotal || 0).toFixed(2);

      // Shipping progress calculation ($100 target)
      const target = 100.00;
      const current = cart.subtotal || 0;
      const pct = Math.min(100, Math.round((current / target) * 100));
      document.getElementById('shippingProgressBar').style.width = pct + '%';
      if (current >= target) {
        document.getElementById('shippingStatusText').innerText = '🎉 Congratulations! You unlocked FREE Air Shipping!';
      } else {
        const remaining = (target - current).toFixed(2);
        document.getElementById('shippingStatusText').innerText = `🚚 You are $${remaining} away from Free Air Shipping!`;
      }

      // Render Cart Items
      const itemsContainer = document.getElementById('cartItemsList');
      if (!cart.items || cart.items.length === 0) {
        itemsContainer.innerHTML = '<div style="text-align:center; padding:32px;"><h4 style="font-size:16px; color:#374151;">Your cart is empty</h4><a href="<?= url('catalog') ?>" style="color:#f05a29; font-weight:700; margin-top:8px; display:inline-block;">Browse Wholesale Catalog &rarr;</a></div>';
        return;
      }

      let html = '';
      cart.items.forEach(item => {
        html += `
          <div style="display:flex; gap:16px; padding:18px 0; border-bottom:1px solid #f1f5f9; align-items:center;">
            <img src="${item.main_image}" style="width:74px; height:74px; object-fit:cover; border-radius:12px; border:1px solid #e2e8f0; background:#f8fafc;">
            <div style="flex:1;">
              <h4 style="font-family:'Inter', system-ui, sans-serif; font-size:14px; font-weight:700; color:#0f172a; margin:0 0 4px 0;">${item.product_title}</h4>
              <div style="font-size:12px; color:#64748b;">Color/Style: <strong style="color:#334155;">${item.color_name || 'Standard'}</strong> | SKU: <span style="font-family:monospace;">${item.sku}</span></div>
              <div style="font-size:14px; font-weight:800; color:#f05a29; margin-top:4px;">$${item.unit_price.toFixed(2)} <span style="font-size:11px; color:#64748b; font-weight:500;">/ piece</span></div>
            </div>
            <div style="display:flex; align-items:center; border:1.5px solid #e2e8f0; border-radius:10px; overflow:hidden; background:#f8fafc;">
              <button onclick="updateQty(${item.product_id}, ${item.variation_id}, ${item.quantity - 1})" style="width:32px; height:36px; border:none; background:transparent; cursor:pointer; font-weight:700; color:#0f172a;">-</button>
              <span style="padding:0 12px; font-size:13px; font-weight:700; color:#0f172a;">${item.quantity}</span>
              <button onclick="updateQty(${item.product_id}, ${item.variation_id}, ${item.quantity + 1})" style="width:32px; height:36px; border:none; background:transparent; cursor:pointer; font-weight:700; color:#0f172a;">+</button>
            </div>
            <div style="font-family:'Inter', system-ui, sans-serif; font-size:16px; font-weight:800; color:#0f172a; min-width:90px; text-align:right;">
              $${item.line_total.toFixed(2)}
            </div>
          </div>
        `;
      });

      itemsContainer.innerHTML = html;

    } catch(e) {}
  }

  async function updateQty(productId, variationId, newQty) {
    try {
      await fetch('<?= url('api/cart/update') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId, variation_id: variationId, quantity: newQty })
      });
      loadCartPage();
      updateHeaderCartCount();
    } catch(e) {}
  }

  async function triggerCheckout() {
    const msgDiv = document.getElementById('checkoutResultMsg');
    msgDiv.innerHTML = '<span style="color:#6b7280;">Processing idempotent order reservation...</span>';

    try {
      const res = await fetch('<?= url('api/checkout/process') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          payment_method: 'razorpay'
        })
      });
      const data = await res.json();
      if (data.success && data.order) {
        msgDiv.innerHTML = `<span style="color:#10b981; font-weight:700;">✓ Order Placed! Order #: ${data.order.order_number} (ID: ${data.order.order_id})</span>`;
        loadCartPage();
        updateHeaderCartCount();
      } else {
        msgDiv.innerHTML = `<span style="color:#ef4444;">Checkout Error: ${data.message}</span>`;
      }
    } catch(e) {
      msgDiv.innerHTML = '<span style="color:#ef4444;">Checkout processing failed.</span>';
    }
  }

  loadCartPage();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
