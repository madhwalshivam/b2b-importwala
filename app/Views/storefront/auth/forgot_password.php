<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="bg-theme-bg py-16 min-h-[70vh] flex items-center justify-center font-sans border-b border-gray-900">
    <div class="container mx-auto px-4 max-w-md">

        <div class="bg-white p-8 rounded-2xl border border-gray-900 shadow-md space-y-6">

            <div class="text-center space-y-2">
                <div
                    class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center mx-auto shadow-xs">
                    <i data-lucide="key" class="w-6 h-6"></i>
                </div>
                <h1 class="text-2xl font-semibold text-gray-900">Forgot Password</h1>
                <p class="text-xs text-gray-500">Enter your account email to receive reset instructions</p>
            </div>

            <?php 
            $displayError = $error ?? (new App\Core\Session())->getFlash('error');
            $displaySuccess = $success ?? (new App\Core\Session())->getFlash('success');
            $displayResetLink = $reset_link ?? (new App\Core\Session())->getFlash('reset_link');
            ?>

            <?php if (!empty($displayError)): ?>
                <div
                    class="bg-red-50 border border-red-200 text-red-700 text-xs font-semibold p-3.5 rounded-xl flex items-center space-x-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
                    <span><?= htmlspecialchars($displayError) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($displaySuccess)): ?>
                <div
                    class="bg-green-50 border border-green-200 text-green-700 text-xs font-semibold p-3.5 rounded-xl flex items-center space-x-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-green-600 shrink-0"></i>
                    <span><?= htmlspecialchars($displaySuccess) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($displayResetLink)): ?>
                <div class="bg-blue-50 border border-blue-200 text-blue-800 text-xs p-4 rounded-xl space-y-2">
                    <div class="font-semibold flex items-center space-x-1.5 text-blue-900">
                        <i data-lucide="key-round" class="w-4 h-4 text-blue-600"></i>
                        <span>Reset Link Generated:</span>
                    </div>
                    <p class="text-[11px] text-blue-700">Click below to proceed to password reset page:</p>
                    <a href="<?= htmlspecialchars($displayResetLink) ?>"
                        class="inline-block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-3 rounded-xl transition text-xs shadow-xs">
                        Reset Account Password &rarr;
                    </a>
                </div>
            <?php endif; ?>

            <form action="<?= url('forgot-password') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Email Address</label>
                    <input type="email" name="email" required placeholder="you@example.com"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                </div>

                <button type="submit"
                    class="w-full h-11 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-md flex items-center justify-center space-x-2">
                    <span>Send Reset Link</span>
                    <i data-lucide="send" class="w-4 h-4"></i>
                </button>
            </form>

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

<?php
include __DIR__ . '/../layouts/footer.php';
?>