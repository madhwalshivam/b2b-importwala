<?php
$googleOn = !empty($googleEnabled) && !empty($googleClientId);
$returnUrl = $returnUrl ?? 'account';
$buttonId = $buttonId ?? 'iw-google-btn-' . substr(md5($returnUrl . rand()), 0, 6);
?>
<?php if ($googleOn): ?>
  <div style="display:flex; flex-direction:column; gap:8px; margin: 4px 0 8px;">
    <div id="<?= htmlspecialchars($buttonId) ?>"></div>
    <p id="<?= htmlspecialchars($buttonId) ?>-error" style="display:none; color:#dc2626; font-size:13px; font-weight:500;"></p>
  </div>
  <script>
    window.iwGoogle = window.iwGoogle || { clientId: <?= json_encode($googleClientId) ?>, csrf: <?= json_encode(csrf_token()) ?> };
    window.iwQueueGoogle = window.iwQueueGoogle || [];
    window.iwQueueGoogle.push({ id: <?= json_encode($buttonId) ?>, returnUrl: <?= json_encode($returnUrl) ?> });
  </script>
<?php endif; ?>
