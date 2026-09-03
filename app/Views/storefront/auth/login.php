<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="bg-theme-bg py-16 min-h-[70vh] flex items-center justify-center font-sans border-b border-gray-900">
    <div class="container mx-auto px-4 max-w-md">

        <div class="bg-white p-8 rounded-2xl border border-gray-900 shadow-md space-y-6">

            <div class="text-center space-y-2">
                <div
                    class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center mx-auto shadow-xs">
                    <i data-lucide="user" class="w-6 h-6"></i>
                </div>
                <h1 class="text-2xl font-semibold text-gray-900">Customer Login</h1>
                <p class="text-xs text-gray-500">Access your ImportWale account, track orders & manage wishlist</p>
            </div>

            <?php if (!empty($error)): ?>
                <div
                    class="bg-red-50 border border-red-200 text-red-700 text-xs font-semibold p-3.5 rounded-xl flex items-center space-x-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($flash = (new App\Core\Session())->getFlash('success')): ?>
                <div
                    class="bg-green-50 border border-green-200 text-green-700 text-xs font-semibold p-3.5 rounded-xl flex items-center space-x-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-green-600 shrink-0"></i>
                    <span><?= htmlspecialchars($flash) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= url('login') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="return" value="<?= htmlspecialchars($returnUrl ?? 'account') ?>">

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Email Address</label>
                    <input type="email" name="email" required placeholder="you@example.com"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Password</label>
                        <a href="<?= url('forgot-password') ?>"
                            class="text-[11px] font-semibold text-red-600 hover:underline">Forgot password?</a>
                    </div>
                    <div class="relative">
                        <input type="password" id="customerPassword" name="password" required placeholder="••••••••"
                            class="w-full h-11 pl-4 pr-12 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                        <button type="button" onclick="togglePassword('customerPassword', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none p-1 rounded-md hover:bg-gray-100 transition"
                            title="Toggle password visibility">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full h-11 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-md flex items-center justify-center space-x-2">
                    <span>Sign In to Account</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>

            <div class="pt-4 border-t border-gray-100 text-center text-xs text-gray-600">
                <span>Don't have an account yet?</span>
                <a href="<?= url('signup') ?>" class="font-semibold text-red-600 hover:underline ml-1">Create an
                    Account</a>
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