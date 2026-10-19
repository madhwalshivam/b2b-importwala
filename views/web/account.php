<?php
$title = 'My Account | ImportWale';
$user = is_array($user ?? null) ? $user : [];
$orders = is_array($orders ?? null) ? $orders : [];
$wishlistItems = is_array($wishlistItems ?? null) ? $wishlistItems : [];
$displayName = trim((string)($user['name'] ?? ''));
if ($displayName === '') {
    $displayName = trim((string)($user['first_name'] ?? '') . ' ' . (string)($user['last_name'] ?? ''));
}
if ($displayName === '') {
    $displayName = 'Customer';
}
$initial = strtoupper(substr($displayName, 0, 1));
$email = (string)($user['email'] ?? ($_SESSION['user_email'] ?? ''));
$phone = (string)($user['phone'] ?? ($_SESSION['user_phone'] ?? ''));
$company = trim((string)($user['company_name'] ?? ''));
$profileComplete = (int)($user['profile_completed'] ?? ($_SESSION['profile_completed'] ?? 0)) === 1;
$wishlistCount = count($wishlistItems);
$orderCount = count($orders);

$statusClass = static function (string $status): string {
    $status = strtolower($status);
    return match ($status) {
        'delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'shipped', 'packed', 'confirmed' => 'bg-orange-50 text-[#c2410c] border-orange-200',
        'cancelled', 'returned', 'failed' => 'bg-rose-50 text-rose-700 border-rose-200',
        'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        default => 'bg-gray-100 text-gray-600 border-gray-200',
    };
};

ob_start();
?>

<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-4 sm:pt-8 pb-24 font-sans text-gray-900">

  <div class="bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-2xs mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center gap-4 min-w-0">
      <div class="w-14 h-14 rounded-2xl bg-[#fff7ed] text-[#f05a29] font-bold text-xl flex items-center justify-center border border-orange-100 shrink-0">
        <?= htmlspecialchars($initial) ?>
      </div>
      <div class="min-w-0">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight">Welcome, <?= htmlspecialchars($displayName) ?></h1>
        <p class="text-xs text-gray-500 mt-0.5 truncate">
          <?= htmlspecialchars($email) ?>
          <?php if ($phone !== ''): ?> · <?= htmlspecialchars($phone) ?><?php endif; ?>
          <?php if ($company !== ''): ?> · <?= htmlspecialchars($company) ?><?php endif; ?>
        </p>
      </div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <?php if (!$profileComplete): ?>
        <a href="<?= url('complete-profile?return=account') ?>"
          class="px-4 py-2 bg-[#fff7ed] hover:bg-orange-100 text-[#c2410c] font-semibold text-xs rounded-xl border border-orange-200 transition">
          Complete profile
        </a>
      <?php endif; ?>
      <a href="<?= url('account/history') ?>"
        class="px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-800 font-semibold text-xs rounded-xl border border-gray-200 transition flex items-center gap-1.5">
        <svg class="w-4 h-4 text-[#f05a29]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        History
      </a>
      <a href="<?= url('wishlist') ?>"
        class="px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-800 font-semibold text-xs rounded-xl border border-gray-200 transition flex items-center gap-1.5">
        <svg class="w-4 h-4 text-[#f05a29]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
        Wishlist (<?= $wishlistCount ?>)
      </a>
      <a href="<?= url('logout') ?>"
        class="px-4 py-2 bg-[#f05a29] hover:bg-[#d94e20] text-white font-semibold text-xs rounded-xl transition">
        Logout
      </a>
    </div>
  </div>

  <?php if (!$profileComplete): ?>
    <div class="mb-6 rounded-2xl border border-orange-200 bg-[#fff7ed] px-4 py-3 text-xs text-[#9a3412] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
      <span>Add your mobile number so the ImportWale team can call you about inquiries and orders.</span>
      <a href="<?= url('complete-profile?return=account') ?>" class="font-semibold text-[#f05a29] hover:underline whitespace-nowrap">Add mobile number →</a>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-8">
    <a href="<?= url('catalog') ?>" class="bg-white border border-gray-200 hover:border-[#f05a29] rounded-2xl p-4 shadow-2xs transition group">
      <div class="w-9 h-9 rounded-xl bg-[#fff7ed] text-[#f05a29] flex items-center justify-center mb-3">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189A3 3 0 0121 8.349"/></svg>
      </div>
      <div class="text-sm font-semibold text-gray-900 group-hover:text-[#f05a29]">Browse catalog</div>
      <div class="text-[11px] text-gray-500 mt-0.5">50,000+ wholesale items</div>
    </a>
    <a href="<?= url('cart') ?>" class="bg-white border border-gray-200 hover:border-[#f05a29] rounded-2xl p-4 shadow-2xs transition group">
      <div class="w-9 h-9 rounded-xl bg-[#fff7ed] text-[#f05a29] flex items-center justify-center mb-3">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
      </div>
      <div class="text-sm font-semibold text-gray-900 group-hover:text-[#f05a29]">My cart</div>
      <div class="text-[11px] text-gray-500 mt-0.5">Review items &amp; checkout</div>
    </a>
    <a href="<?= url('wishlist') ?>" class="bg-white border border-gray-200 hover:border-[#f05a29] rounded-2xl p-4 shadow-2xs transition group">
      <div class="w-9 h-9 rounded-xl bg-[#fff7ed] text-[#f05a29] flex items-center justify-center mb-3">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
      </div>
      <div class="text-sm font-semibold text-gray-900 group-hover:text-[#f05a29]">Wishlist</div>
      <div class="text-[11px] text-gray-500 mt-0.5"><?= $wishlistCount ?> saved item<?= $wishlistCount === 1 ? '' : 's' ?></div>
    </a>
    <a href="<?= url('account/history') ?>" class="bg-white border border-gray-200 hover:border-[#f05a29] rounded-2xl p-4 shadow-2xs transition group">
      <div class="w-9 h-9 rounded-xl bg-[#fff7ed] text-[#f05a29] flex items-center justify-center mb-3">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div class="text-sm font-semibold text-gray-900 group-hover:text-[#f05a29]">History</div>
      <div class="text-[11px] text-gray-500 mt-0.5">Recently viewed products</div>
    </a>
    <button type="button" onclick="typeof openRfqModal === 'function' && openRfqModal(null, true)"
      class="bg-white border border-gray-200 hover:border-[#f05a29] rounded-2xl p-4 shadow-2xs transition group text-left w-full">
      <div class="w-9 h-9 rounded-xl bg-[#fff7ed] text-[#f05a29] flex items-center justify-center mb-3">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      </div>
      <div class="text-sm font-semibold text-gray-900 group-hover:text-[#f05a29]">Custom quote</div>
      <div class="text-[11px] text-gray-500 mt-0.5">Send an RFQ to our team</div>
    </button>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-8 space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
          <svg class="w-5 h-5 text-[#f05a29]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          My order history
        </h2>
        <span class="text-xs text-gray-500 font-medium"><?= $orderCount ?> total order<?= $orderCount === 1 ? '' : 's' ?></span>
      </div>

      <?php if (empty($orders)): ?>
        <div class="bg-white p-10 sm:p-12 rounded-2xl border border-gray-200 text-center space-y-3 shadow-2xs">
          <div class="w-14 h-14 bg-[#fff7ed] text-[#f05a29] rounded-full flex items-center justify-center mx-auto">
            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          </div>
          <h3 class="text-sm font-semibold text-gray-800">No orders placed yet</h3>
          <p class="text-xs text-gray-500">Explore the wholesale catalog and place your first order.</p>
          <a href="<?= url('catalog') ?>" class="inline-block px-6 py-2.5 bg-[#f05a29] hover:bg-[#d94e20] text-white font-semibold text-xs rounded-xl transition">Start sourcing</a>
        </div>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($orders as $order): ?>
            <?php
              $orderStatus = (string)($order['order_status'] ?? 'pending');
              $payStatus = (string)($order['payment_status'] ?? '');
              $created = !empty($order['created_at']) ? date('M d, Y · h:i A', strtotime((string)$order['created_at'])) : '';
            ?>
            <div class="bg-white p-5 rounded-2xl border border-gray-200 hover:border-orange-200 transition shadow-2xs space-y-3">
              <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-3">
                <div>
                  <span class="text-xs font-mono font-semibold text-[#f05a29]">ORDER #<?= htmlspecialchars((string)($order['order_number'] ?? $order['id'] ?? '')) ?></span>
                  <?php if ($created !== ''): ?>
                    <p class="text-[11px] text-gray-500 mt-0.5"><?= htmlspecialchars($created) ?></p>
                  <?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                  <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider border <?= $statusClass($orderStatus) ?>">
                    <?= htmlspecialchars($orderStatus) ?>
                  </span>
                  <span class="text-sm font-semibold text-gray-900"><?= format_price($order['total_amount'] ?? 0) ?></span>
                </div>
              </div>
              <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-600">
                <span>Payment: <strong class="uppercase font-semibold text-gray-800"><?= htmlspecialchars((string)($order['payment_method'] ?? '—')) ?></strong>
                  <?php if ($payStatus !== ''): ?>
                    <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase border <?= $statusClass($payStatus) ?>"><?= htmlspecialchars($payStatus) ?></span>
                  <?php endif; ?>
                </span>
                <a href="<?= url('checkout/success/' . ($order['id'] ?? '')) ?>" class="font-semibold text-[#f05a29] hover:underline">View order</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="lg:col-span-4 space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
          <svg class="w-5 h-5 text-[#f05a29]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
          Saved wishlist
        </h2>
        <a href="<?= url('wishlist') ?>" class="text-xs text-[#f05a29] font-semibold hover:underline">View all</a>
      </div>

      <?php if (empty($wishlistItems)): ?>
        <div class="bg-white p-8 rounded-2xl border border-gray-200 text-center space-y-2 shadow-2xs">
          <div class="w-12 h-12 bg-[#fff7ed] text-[#f05a29] rounded-full flex items-center justify-center mx-auto">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
          </div>
          <p class="text-xs text-gray-500">Your wishlist is empty.</p>
          <a href="<?= url('catalog') ?>" class="inline-block text-xs font-semibold text-[#f05a29] hover:underline">Browse catalog</a>
        </div>
      <?php else: ?>
        <div class="bg-white p-4 rounded-2xl border border-gray-200 divide-y divide-gray-100 shadow-2xs">
          <?php foreach (array_slice($wishlistItems, 0, 8) as $item): ?>
            <?php
              $itemName = (string)($item['name'] ?? $item['title'] ?? 'Product');
              $itemSlug = (string)($item['slug'] ?? $item['id'] ?? '');
              $itemImg = $item['main_image'] ?? '';
              if ($itemImg === '' && function_exists('get_product_images')) {
                  $imgs = get_product_images($item);
                  $itemImg = $imgs[0] ?? '';
              }
              $itemImgUrl = $itemImg !== '' ? (preg_match('~^https?://~i', $itemImg) ? $itemImg : asset($itemImg)) : asset('assets/images/placeholder.jpg');
              $itemPrice = $item['sale_price'] ?? $item['price'] ?? $item['base_price'] ?? 0;
            ?>
            <div class="py-3 flex items-center gap-3 first:pt-0 last:pb-0">
              <img src="<?= htmlspecialchars($itemImgUrl) ?>" alt=""
                class="w-12 h-12 object-cover rounded-lg border border-gray-200 shrink-0 bg-gray-50"
                onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">
              <div class="flex-1 min-w-0">
                <h4 class="text-xs font-semibold text-gray-900 truncate"><?= htmlspecialchars($itemName) ?></h4>
                <p class="text-xs font-semibold text-[#f05a29] mt-0.5"><?= format_price($itemPrice) ?></p>
              </div>
              <a href="<?= url('product/' . $itemSlug) ?>"
                class="p-1.5 bg-gray-50 hover:bg-[#f05a29] hover:text-white rounded-lg text-gray-600 transition border border-gray-200 hover:border-[#f05a29]">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
