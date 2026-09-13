<?php
$title = "Reset Password | ImportWale";
ob_start();
?>

<div style="max-width: 440px; margin: 40px auto 80px auto; padding: 0 16px;">
  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 32px;">
    
    <div style="text-align: center; margin-bottom: 28px;">
      <h1 style="font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 6px;">Set New Password</h1>
      <p style="font-size: 13.5px; color: #6b7280;">Please enter your new account password below.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 500; margin-bottom: 20px;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($tokenValid)): ?>
      <form action="<?= url('reset-password') ?>" method="POST" style="display: flex; flex-direction: column; gap: 18px;">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

        <div>
          <label style="display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 6px;">New Password</label>
          <div style="position: relative;">
            <input type="password" id="custNewPass" name="password" required placeholder="Minimum 6 characters"
              style="width: 100%; height: 44px; padding: 0 40px 0 14px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; color: #111827; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
              onfocus="this.style.borderColor='#f05a29'; this.style.boxShadow='0 0 0 3px rgba(240, 90, 41, 0.15)';"
              onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
            <button type="button" onclick="togglePassword('custNewPass', this)"
              style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px; font-size: 14px;"
              title="Toggle password visibility">👁</button>
          </div>
        </div>

        <div>
          <label style="display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 6px;">Confirm New Password</label>
          <div style="position: relative;">
            <input type="password" id="custConfPass" name="confirm_password" required placeholder="Re-enter new password"
              style="width: 100%; height: 44px; padding: 0 40px 0 14px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; color: #111827; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
              onfocus="this.style.borderColor='#f05a29'; this.style.boxShadow='0 0 0 3px rgba(240, 90, 41, 0.15)';"
              onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
            <button type="button" onclick="togglePassword('custConfPass', this)"
              style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px; font-size: 14px;"
              title="Toggle password visibility">👁</button>
          </div>
        </div>

        <button type="submit"
          style="width: 100%; height: 46px; background: #f05a29; color: #ffffff; font-weight: 600; font-size: 14px; border: none; border-radius: 10px; cursor: pointer; transition: background 0.2s; margin-top: 4px;"
          onmouseover="this.style.background='#d8481b'"
          onmouseout="this.style.background='#f05a29'">
          Save New Password
        </button>
      </form>
    <?php else: ?>
      <div style="text-align: center; padding: 16px 0;">
        <p style="font-size: 13px; color: #dc2626; font-weight: 600; margin-bottom: 16px;">The password reset link is invalid or has expired.</p>
        <a href="<?= url('forgot-password') ?>" style="display: inline-block; background: #f05a29; color: #ffffff; font-weight: 600; font-size: 13px; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
          Request New Reset Link
        </a>
      </div>
    <?php endif; ?>

    <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #f3f4f6; text-align: center; font-size: 13.5px; color: #6b7280;">
      <a href="<?= url('login') ?>" style="font-weight: 600; color: #f05a29; text-decoration: none;">&larr; Back to Login</a>
    </div>

  </div>
</div>

<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.textContent = isPassword ? '🙈' : '👁';
    }
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
