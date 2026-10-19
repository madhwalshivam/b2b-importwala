<?php
$title = $title ?? 'Login to view more products | ImportWale';
ob_start();
$returnUrl = $returnUrl ?? 'account';
?>
<div id="iw-guest-limit-modal" style="position:fixed; inset:0; background:rgba(17,24,39,.45); z-index:200; display:flex; align-items:center; justify-content:center; padding:16px;">
  <div style="background:#fff; max-width:420px; width:100%; border-radius:16px; padding:28px; text-align:center; box-shadow:0 20px 50px rgba(0,0,0,.15); position:relative;">
    <button type="button" aria-label="Close" title="Close" onclick="this.closest('#iw-guest-limit-modal').style.display='none'"
      style="position:absolute; right:12px; top:12px; width:36px; height:36px; border:1px solid #e5e7eb; background:#f3f4f6; border-radius:999px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#111827;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
        <path d="M6 6l12 12M18 6L6 18"/>
      </svg>
    </button>
    <h1 style="font-size:22px; font-weight:700; margin:0 40px 8px; color:#111827;">Login to view more products</h1>
    <p style="font-size:14px; color:#6b7280; margin-bottom:18px;">Login to view more products and for a better experience.</p>
    <?php
      $buttonId = 'iw-google-limit';
      include __DIR__ . '/partials/google_button.php';
    ?>
    <div style="display:flex; gap:10px; margin-top:14px;">
      <a href="<?= url('login?return=' . rawurlencode($returnUrl)) ?>" style="flex:1; height:44px; display:flex; align-items:center; justify-content:center; background:#f05a29; color:#fff; border-radius:10px; font-weight:600; text-decoration:none;">Login</a>
      <a href="<?= url('signup?return=' . rawurlencode($returnUrl)) ?>" style="flex:1; height:44px; display:flex; align-items:center; justify-content:center; border:1px solid #d1d5db; border-radius:10px; font-weight:600; text-decoration:none; color:#111827;">Sign up</a>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
