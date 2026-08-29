<?php
$title = "My Wholesale Wishlist | ImportWala";
ob_start();

$totalCount = count($products ?? []);
?>

<div style="max-w: 1200px; margin: 0 auto; padding-top: 12px;">

  <!-- Page Header -->
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; border-bottom:1px solid #f1f5f9; padding-bottom:16px;">
    <div>
      <h1 style="font-family:'Inter', system-ui, sans-serif; font-size:26px; font-weight:800; color:#0f172a; margin:0 0 4px 0;">My Wholesale Wishlist</h1>
      <p style="font-size:13px; color:#64748b; margin:0;">Save products for bulk enquiry or quick reordering. Maximum limit: <?= $maxLimit ?> items.</p>
    </div>

    <?php if ($totalCount > 0): ?>
      <a href="<?= htmlspecialchars($whatsappShareUrl) ?>" target="_blank" rel="noopener noreferrer" 
         style="display:inline-flex; align-items:center; gap:8px; background:#25D366; color:#ffffff; font-weight:700; font-size:14px; padding:12px 22px; border-radius:12px; text-decoration:none; box-shadow:0 4px 12px rgba(37,211,102,0.25); transition:transform 0.2s ease;">
        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
        Send Wishlist on WhatsApp
      </a>
    <?php endif; ?>
  </div>

  <?php if (empty($products)): ?>
    <div style="background:#fff; border:1px solid #f1f5f9; border-radius:24px; padding:60px 24px; text-align:center; box-shadow:0 4px 20px rgba(0,0,0,0.03);">
      <div style="width:72px; height:72px; background:#fff7ed; color:#f05a29; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px auto;">
        <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
      </div>
      <h3 style="font-size:20px; font-weight:800; color:#0f172a; margin:0 0 8px 0;">Your Wishlist is Empty</h3>
      <p style="font-size:14px; color:#64748b; margin:0 0 20px 0;">Heart icons par click karke apne pasandida wholesale products wishlist mein add karein.</p>
      <a href="<?= url('catalog') ?>" style="display:inline-block; background:#0f172a; color:#fff; font-weight:700; padding:12px 24px; border-radius:12px; text-decoration:none; font-size:14px;">
        Browse Wholesale Catalog &rarr;
      </a>
    </div>
  <?php else: ?>

    <!-- Limit Bar -->
    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px 20px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; font-size:13px; color:#475569;">
      <span>Currently saved: <strong style="color:#0f172a;"><?= $totalCount ?></strong> / <?= $maxLimit ?> items</span>
      <span style="color:#64748b;">Click "Send Wishlist on WhatsApp" to get a direct wholesale quotation for all items.</span>
    </div>

    <!-- Wishlist Grid -->
    <div class="product-grid" id="wishlistItemsContainer">
      <?php foreach ($products as $product): ?>
        <?php require __DIR__ . '/partials/product_card.php'; ?>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
