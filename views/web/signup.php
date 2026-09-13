<?php
$title = "Create Account | ImportWale";
ob_start();
?>

<div style="max-width: 480px; margin: 40px auto 80px auto; padding: 0 16px;">
  <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 32px;">
    
    <div style="text-align: center; margin-bottom: 28px;">
      <h1 style="font-size: 24px; font-weight: 700; color: #111827; margin-bottom: 6px;">Create Account</h1>
      <p style="font-size: 13.5px; color: #6b7280;">Fill in your details below to get started.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 500; margin-bottom: 20px;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form action="<?= url('signup') ?>" method="POST" style="display: flex; flex-direction: column; gap: 16px;">
      <?= csrf_field() ?>

      <div>
        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 6px;">Full Name</label>
        <input type="text" name="name" required placeholder="John Doe"
          style="width: 100%; height: 44px; padding: 0 14px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; color: #111827; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
          onfocus="this.style.borderColor='#f05a29'; this.style.boxShadow='0 0 0 3px rgba(240, 90, 41, 0.15)';"
          onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
      </div>

      <div>
        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 6px;">Email Address</label>
        <input type="email" name="email" required placeholder="you@example.com"
          style="width: 100%; height: 44px; padding: 0 14px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; color: #111827; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
          onfocus="this.style.borderColor='#f05a29'; this.style.boxShadow='0 0 0 3px rgba(240, 90, 41, 0.15)';"
          onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
      </div>

      <div>
        <label style="display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 6px;">Phone Number (Optional)</label>
        <input type="tel" name="phone" placeholder="+91 98765 43210"
          style="width: 100%; height: 44px; padding: 0 14px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; color: #111827; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
          onfocus="this.style.borderColor='#f05a29'; this.style.boxShadow='0 0 0 3px rgba(240, 90, 41, 0.15)';"
          onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 14px;">
        <div>
          <label style="display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 6px;">Password</label>
          <div style="position: relative;">
            <input type="password" id="signupPassword" name="password" required placeholder="Min 6 chars"
              style="width: 100%; height: 44px; padding: 0 36px 0 14px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; color: #111827; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
              onfocus="this.style.borderColor='#f05a29'; this.style.boxShadow='0 0 0 3px rgba(240, 90, 41, 0.15)';"
              onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
            <button type="button" onclick="togglePassword('signupPassword', this)"
              style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px; font-size: 14px;"
              title="Toggle password visibility">👁</button>
          </div>
        </div>

        <div>
          <label style="display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 6px;">Confirm Password</label>
          <div style="position: relative;">
            <input type="password" id="signupConfirmPassword" name="confirm_password" required placeholder="Repeat password"
              style="width: 100%; height: 44px; padding: 0 36px 0 14px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; color: #111827; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
              onfocus="this.style.borderColor='#f05a29'; this.style.boxShadow='0 0 0 3px rgba(240, 90, 41, 0.15)';"
              onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';">
            <button type="button" onclick="togglePassword('signupConfirmPassword', this)"
              style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px; font-size: 14px;"
              title="Toggle password visibility">👁</button>
          </div>
        </div>
      </div>

      <button type="submit"
        style="width: 100%; height: 46px; background: #f05a29; color: #ffffff; font-weight: 600; font-size: 14px; border: none; border-radius: 10px; cursor: pointer; transition: background 0.2s; margin-top: 4px;"
        onmouseover="this.style.background='#d8481b'"
        onmouseout="this.style.background='#f05a29'">
        Register Account
      </button>
    </form>

    <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #f3f4f6; text-align: center; font-size: 13.5px; color: #6b7280;">
      <span>Already have an account?</span>
      <a href="<?= url('login') ?>" style="font-weight: 600; color: #f05a29; text-decoration: none; margin-left: 4px;">Sign In</a>
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
