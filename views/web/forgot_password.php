<?php
$title = "Forgot Password | ImportWale";
ob_start();
?>

<div style="max-width: 440px; margin: 40px auto 80px auto; padding: 0 16px;">
  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 32px;">
    
    <div style="text-align: center; margin-bottom: 28px;">
      <h1 style="font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 6px;">Forgot Password</h1>
      <p style="font-size: 13.5px; color: #6b7280;">Enter your email address to receive a password reset link.</p>
    </div>

    <?php 
    $displayError = $error ?? (new App\Core\Session())->getFlash('error');
    $displaySuccess = $success ?? (new App\Core\Session())->getFlash('success');
    $displayResetLink = $reset_link ?? (new App\Core\Session())->getFlash('reset_link');
    ?>

    <?php if (!empty($displayError)): ?>
      <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 500; margin-bottom: 20px;">
        <?= htmlspecialchars($displayError) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($displaySuccess)): ?>
      <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 500; margin-bottom: 20px;">
        <?= htmlspecialchars($displaySuccess) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($displayResetLink)): ?>
      <div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 16px; border-radius: 10px; font-size: 13px; margin-bottom: 20px;">
        <div style="font-weight: 700; margin-bottom: 6px;">Reset Link Generated:</div>
        <p style="margin-bottom: 12px; font-size: 12px; color: #3b82f6;">Click below to reset your password:</p>
        <a href="<?= htmlspecialchars($displayResetLink) ?>" style="display: block; text-align: center; background: #2563eb; color: #ffffff; font-weight: 600; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-size: 13px;">
          Reset Account Password &rarr;
        </a>
      </div>
    <?php endif; ?>

    <form action="<?= url('forgot-password') ?>" method="POST" style="display: flex; flex-direction: column; gap: 18px;">
      <?= csrf_field() ?>

      <div>
        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 6px;">Email Address</label>
        <input type="email" name="email" required placeholder="you@example.com"
          style="width: 100%; height: 44px; padding: 0 14px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; color: #111827; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
          onfocus="this.style.borderColor='#f05a29'; this.style.boxShadow='0 0 0 3px rgba(240, 90, 41, 0.15)';"
          onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
      </div>

      <button type="submit"
        style="width: 100%; height: 46px; background: #f05a29; color: #ffffff; font-weight: 600; font-size: 14px; border: none; border-radius: 10px; cursor: pointer; transition: background 0.2s; margin-top: 4px;"
        onmouseover="this.style.background='#d8481b'"
        onmouseout="this.style.background='#f05a29'">
        Send Reset Link
      </button>
    </form>

    <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #f3f4f6; text-align: center; font-size: 13.5px; color: #6b7280;">
      <a href="<?= url('login') ?>" style="font-weight: 600; color: #f05a29; text-decoration: none;">&larr; Back to Login</a>
    </div>

  </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
