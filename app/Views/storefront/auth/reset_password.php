<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="bg-theme-bg py-16 min-h-[70vh] flex items-center justify-center font-sans border-b border-gray-900">
    <div class="container mx-auto px-4 max-w-md">

        <div class="bg-white p-8 rounded-2xl border border-gray-900 shadow-md space-y-6">

            <div class="text-center space-y-2">
                <div
                    class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center mx-auto shadow-xs">
                    <i data-lucide="lock" class="w-6 h-6"></i>
                </div>
                <h1 class="text-2xl font-semibold text-gray-900">Set New Password</h1>
                <p class="text-xs text-gray-500">Please choose a new password for your account</p>
            </div>

            <?php if (!empty($error)): ?>
                <div
                    class="bg-red-50 border border-red-200 text-red-700 text-xs font-semibold p-3.5 rounded-xl flex items-center space-x-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($tokenValid)): ?>
                <form action="<?= url('reset-password') ?>" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">New Password</label>
                        <div class="relative">
                            <input type="password" id="custNewPass" name="password" required
                                placeholder="Minimum 6 characters"
                                class="w-full h-11 pl-4 pr-12 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                            <button type="button" onclick="togglePassword('custNewPass', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none p-1 rounded-md hover:bg-gray-100 transition"
                                title="Toggle password visibility">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Confirm New
                            Password</label>
                        <div class="relative">
                            <input type="password" id="custConfPass" name="confirm_password" required
                                placeholder="Re-enter new password"
                                class="w-full h-11 pl-4 pr-12 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                            <button type="button" onclick="togglePassword('custConfPass', this)"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none p-1 rounded-md hover:bg-gray-100 transition"
                                title="Toggle password visibility">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full h-11 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-md flex items-center justify-center space-x-2">
                        <span>Reset & Save Password</span>
                        <i data-lucide="check" class="w-4 h-4"></i>
                    </button>
                </form>
            <?php else: ?>
                <div class="text-center space-y-4 py-4">
                    <p class="text-xs text-red-600 font-semibold">The password reset link is invalid or has expired.</p>
                    <a href="<?= url('forgot-password') ?>"
                        class="inline-block bg-red-600 hover:bg-red-700 text-white font-semibold text-xs py-2.5 px-4 rounded-xl transition shadow-xs">
                        Request New Reset Link
                    </a>
                </div>
            <?php endif; ?>

            <div class="pt-4 border-t border-gray-100 text-center text-xs text-gray-600">
                <a href="<?= url('login') ?>"
                    class="font-semibold text-red-600 hover:underline inline-flex items-center justify-center space-x-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Back to Customer Login</span>
                </a>
            </div>

        </div>

    </div>
</div>

<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.innerHTML = `<i data-lucide="${isPassword ? 'eye-off' : 'eye'}" class="w-4 h-4"></i>`;
        if (window.lucide) {
            lucide.createIcons({
                nameAttr: 'data-lucide',
                el: btn
            });
        }
    }
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>