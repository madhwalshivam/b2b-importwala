<?php
$title = "Customer Login | ImportWale";
ob_start();
?>

<div style="max-width: 440px; margin: 40px auto 80px auto; padding: 0 16px;">
  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 32px;">
    
    <div style="text-align: center; margin-bottom: 28px;">
      <h1 style="font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 6px;">Sign In</h1>
      <p style="font-size: 13.5px; color: #6b7280;">Welcome back! Enter your details to log in.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 500; margin-bottom: 20px;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($flash = (new App\Core\Session())->getFlash('success')): ?>
      <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 500; margin-bottom: 20px;">
        <?= htmlspecialchars($flash) ?>
      </div>
    <?php endif; ?>

    <form action="<?= url('login') ?>" method="POST" style="display: flex; flex-direction: column; gap: 18px;">
      <?= csrf_field() ?>
      <input type="hidden" name="return" value="<?= htmlspecialchars($returnUrl ?? 'account') ?>">

      <div>
        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 6px;">Email Address</label>
        <input type="email" name="email" required placeholder="you@example.com"
          style="width: 100%; height: 44px; padding: 0 14px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; color: #111827; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
          onfocus="this.style.borderColor='#f05a29'; this.style.boxShadow='0 0 0 3px rgba(240, 90, 41, 0.15)';"
          onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
      </div>

      <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
          <label style="font-size: 12.5px; font-weight: 600; color: #374151;">Password</label>
          <a href="<?= url('forgot-password') ?>" style="font-size: 12.5px; font-weight: 600; color: #f05a29; text-decoration: none;">Forgot password?</a>
        </div>
        <div style="position: relative;">
          <input type="password" id="customerPassword" name="password" required placeholder="••••••••"
            style="width: 100%; height: 44px; padding: 0 40px 0 14px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; color: #111827; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
            onfocus="this.style.borderColor='#f05a29'; this.style.boxShadow='0 0 0 3px rgba(240, 90, 41, 0.15)';"
            onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
          <button type="button" onclick="togglePassword('customerPassword', this)"
            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px; font-size: 14px;"
            title="Toggle password visibility">👁</button>
        </div>
      </div>

      <button type="submit"
        style="width: 100%; height: 46px; background: #f05a29; color: #ffffff; font-weight: 600; font-size: 14px; border: none; border-radius: 10px; cursor: pointer; transition: background 0.2s; margin-top: 4px;"
        onmouseover="this.style.background='#d8481b'"
        onmouseout="this.style.background='#f05a29'">
        Sign In
      </button>
    </form>

    <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #f3f4f6; text-align: center; font-size: 13.5px; color: #6b7280;">
      <span>Don't have an account?</span>
      <a href="<?= url('signup') ?>" style="font-weight: 600; color: #f05a29; text-decoration: none; margin-left: 4px;">Create an account</a>
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
