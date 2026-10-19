<?php
$title = $title ?? 'Complete your profile | ImportWale';
$otpStep = !empty($otpStep);
ob_start();
?>
<div style="max-width: 480px; margin: 40px auto 80px auto; padding: 0 16px;">
  <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:32px;">
    <h1 style="font-size:24px; font-weight:700; color:#111827; margin-bottom:6px;">Complete your profile</h1>
    <p style="font-size:13.5px; color:#6b7280; margin-bottom:20px;">We need a mobile number so the ImportWale team can call you about inquiries and orders.</p>

    <?php if (!empty($error)): ?>
      <div style="background:#fef2f2; border:1px solid #fecaca; color:#dc2626; padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($debugCode)): ?>
      <div style="background:#fff7ed; border:1px solid #fdba74; color:#9a3412; padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:16px;">Local testing code: <?= htmlspecialchars((string)$debugCode) ?></div>
    <?php endif; ?>

    <?php if ($otpStep): ?>
      <form method="POST" action="<?= url('complete-profile') ?>" style="display:flex; flex-direction:column; gap:14px;">
        <?= csrf_field() ?>
        <input type="hidden" name="return" value="<?= htmlspecialchars($returnUrl ?? 'account') ?>">
        <input type="hidden" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
        <input type="hidden" name="company_name" value="<?= htmlspecialchars($user['company_name'] ?? '') ?>">
        <input type="hidden" name="gst_number" value="<?= htmlspecialchars($user['gst_number'] ?? '') ?>">
        <input type="hidden" name="contact_consent" value="1">
        <input type="hidden" name="action" value="verify_otp">
        <label style="font-size:12.5px; font-weight:600;">Verification code</label>
        <input name="otp" inputmode="numeric" maxlength="6" required placeholder="6-digit code" style="height:44px; border:1px solid #d1d5db; border-radius:10px; padding:0 14px;">
        <button type="submit" style="height:46px; background:#f05a29; color:#fff; border:none; border-radius:10px; font-weight:600;">Verify mobile</button>
      </form>
      <form method="POST" action="<?= url('complete-profile') ?>" style="margin-top:12px;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="resend">
        <button type="submit" style="background:none; border:none; color:#f05a29; font-weight:600; cursor:pointer;">Resend code</button>
      </form>
    <?php else: ?>
      <form method="POST" action="<?= url('complete-profile') ?>" style="display:flex; flex-direction:column; gap:14px;">
        <?= csrf_field() ?>
        <input type="hidden" name="return" value="<?= htmlspecialchars($returnUrl ?? 'account') ?>">
        <div>
          <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:6px;">Mobile number</label>
          <input type="tel" name="phone" required value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="9876543210" style="width:100%; height:44px; border:1px solid #d1d5db; border-radius:10px; padding:0 14px;">
        </div>
        <div>
          <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:6px;">Company name (optional)</label>
          <input type="text" name="company_name" value="<?= htmlspecialchars($user['company_name'] ?? '') ?>" style="width:100%; height:44px; border:1px solid #d1d5db; border-radius:10px; padding:0 14px;">
        </div>
        <div>
          <label style="display:block; font-size:12.5px; font-weight:600; margin-bottom:6px;">GST Number (optional)</label>
          <input type="text" name="gst_number" maxlength="15" placeholder="22AAAAA0000A1Z5" value="<?= htmlspecialchars($user['gst_number'] ?? '') ?>" autocomplete="off" autocapitalize="characters" style="width:100%; height:44px; border:1px solid #d1d5db; border-radius:10px; padding:0 14px; text-transform:uppercase;">
        </div>
        <label style="display:flex; gap:8px; align-items:flex-start; font-size:13px; color:#374151;">
          <input type="checkbox" name="contact_consent" value="1" required <?= !empty($user['contact_consent']) ? 'checked' : '' ?>>
          <span>I agree to be contacted by Importwale via call/WhatsApp regarding my inquiries and orders.</span>
        </label>
        <p style="font-size:12px; color:#6b7280;">By continuing you accept the <a href="<?= url('terms-and-conditions') ?>" style="color:#f05a29;">Terms</a> and <a href="<?= url('privacy-policy') ?>" style="color:#f05a29;">Privacy Policy</a>.</p>
        <button type="submit" style="height:46px; background:#f05a29; color:#fff; border:none; border-radius:10px; font-weight:600;"><?= !empty($otpRequired) ? 'Send verification code' : 'Save and continue' ?></button>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
