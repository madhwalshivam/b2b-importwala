<?php
$title = 'Recently Viewed | ImportWale';
$items = is_array($items ?? null) ? $items : [];
$total = (int)($total ?? count($items));
$page = max(1, (int)($page ?? 1));
$pages = max(1, (int)($pages ?? 1));
$isHistoryPage = true;
ob_start();
?>

<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-4 sm:pt-8 pb-24 font-sans text-gray-900">
  <div class="iw-history-head">
    <h1 class="iw-history-title">
      <svg class="iw-history-title-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <span>Recently Viewed</span>
    </h1>
    <div class="iw-history-head-actions">
      <?php if ($total > 0): ?>
        <button type="button" id="iwHistoryClearBtn" class="iw-history-clear-btn">Clear history</button>
        <form id="iwHistoryClearForm" method="POST" action="<?= url('account/history/clear') ?>">
          <?= csrf_field() ?>
        </form>
      <?php endif; ?>
      <a href="<?= url('account') ?>" class="iw-history-account-link">Account</a>
    </div>
  </div>

  <div id="iwHistoryEmpty" style="display: <?= empty($items) ? 'block' : 'none' ?>;"
    class="bg-white border border-gray-200 rounded-2xl p-8 sm:p-12 text-center max-w-md mx-auto my-8 shadow-2xs space-y-4">
    <div class="w-16 h-16 bg-[#fff7ed] text-[#f05a29] rounded-full flex items-center justify-center mx-auto">
      <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </div>
    <h2 class="text-lg font-semibold text-gray-900">No recently viewed products</h2>
    <p class="text-xs text-gray-500">Products you open while logged in will show up here, on this device and any other.</p>
    <a href="<?= url('catalog') ?>" class="inline-block px-6 py-3 bg-[#f05a29] hover:bg-[#d94e20] text-white text-xs font-semibold rounded-xl shadow-xs transition">Browse products</a>
  </div>

  <div id="iwHistoryGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5 sm:gap-4" style="display: <?= !empty($items) ? 'grid' : 'none' ?>;">
    <?php foreach ($items as $product): ?>
      <?php
        $pId = (int)($product['id'] ?? 0);
        $viewedLabel = htmlspecialchars((string)($product['viewed_label'] ?? ''));
      ?>
      <div class="iw-history-card-wrap" id="iwHistoryCard_<?= $pId ?>" data-product-id="<?= $pId ?>">
        <?php require __DIR__ . '/partials/product_card.php'; ?>
        <?php if ($viewedLabel !== ''): ?>
          <div class="iw-history-viewed"><?= $viewedLabel ?></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($pages > 1 && !empty($items)): ?>
    <div class="flex flex-wrap gap-2 justify-center mt-8 text-xs">
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a class="px-3 py-1.5 rounded-lg border <?= $i === $page ? 'bg-[#f05a29] text-white border-[#f05a29]' : 'bg-white text-gray-700 border-gray-200' ?>"
          href="<?= url('account/history?page=' . $i) ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<div id="iwHistoryClearModal" class="iw-history-modal" hidden>
  <div class="iw-history-modal-card" role="dialog" aria-modal="true" aria-labelledby="iwHistoryClearTitle">
    <h2 id="iwHistoryClearTitle">Clear history?</h2>
    <p>This will remove all recently viewed products from your account.</p>
    <div class="iw-history-modal-actions">
      <button type="button" id="iwHistoryClearCancel" class="iw-history-modal-cancel">Cancel</button>
      <button type="button" id="iwHistoryClearConfirm" class="iw-history-modal-confirm">Clear history</button>
    </div>
  </div>
</div>

<script>
window.iwHistoryRemove = function (productId, btn) {
  productId = parseInt(productId || '0', 10);
  if (!productId) return;
  var body = new FormData();
  body.append('product_id', String(productId));
  body.append('_csrf_token', <?= json_encode(csrf_token()) ?>);
  fetch(<?= json_encode(url('account/history/remove')) ?>, {
    method: 'POST',
    body: body,
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': <?= json_encode(csrf_token()) ?> }
  }).then(function (r) { return r.json(); }).then(function (data) {
    if (!data || !data.success) return;
    var card = document.getElementById('iwHistoryCard_' + productId);
    if (card) card.remove();
    var left = document.querySelectorAll('.iw-history-card-wrap').length;
    if (left === 0) {
      var grid = document.getElementById('iwHistoryGrid');
      var empty = document.getElementById('iwHistoryEmpty');
      var clearBtn = document.getElementById('iwHistoryClearBtn');
      if (grid) grid.style.display = 'none';
      if (empty) empty.style.display = 'block';
      if (clearBtn) clearBtn.style.display = 'none';
    }
  }).catch(function () {});
};

(function () {
  var modal = document.getElementById('iwHistoryClearModal');
  var openBtn = document.getElementById('iwHistoryClearBtn');
  var cancelBtn = document.getElementById('iwHistoryClearCancel');
  var confirmBtn = document.getElementById('iwHistoryClearConfirm');
  var form = document.getElementById('iwHistoryClearForm');
  function closeModal() {
    if (modal) modal.hidden = true;
  }
  function openModal() {
    if (modal) modal.hidden = false;
  }
  if (openBtn) openBtn.addEventListener('click', openModal);
  if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
  if (modal) modal.addEventListener('click', function (e) {
    if (e.target === modal) closeModal();
  });
  if (confirmBtn) confirmBtn.addEventListener('click', function () {
    if (form) form.submit();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeModal();
  });
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
