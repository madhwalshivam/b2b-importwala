<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="bg-theme-bg py-16 min-h-[70vh] flex items-center justify-center font-sans border-b border-gray-900">
    <div class="container mx-auto px-4 max-w-md">

        <div class="bg-white p-8 rounded-2xl border border-gray-900 shadow-md space-y-6">

            <div class="text-center space-y-2">
                <div
                    class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center mx-auto shadow-xs">
                    <i data-lucide="user-plus" class="w-6 h-6"></i>
                </div>
                <h1 class="text-2xl font-semibold text-gray-900">Create Account</h1>
                <p class="text-xs text-gray-500">Sign up to buy EV accessories with guaranteed fitment</p>
            </div>

            <?php if (!empty($error)): ?>
                <div
                    class="bg-red-50 border border-red-200 text-red-700 text-xs font-semibold p-3.5 rounded-xl flex items-center space-x-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= url('signup') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Full Name</label>
                    <input type="text" name="name" required placeholder="John Doe"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Email Address</label>
                    <input type="email" name="email" required placeholder="you@example.com"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Password</label>
                    <input type="password" name="password" required placeholder="At least 6 characters"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1.5">Confirm Password</label>
                    <input type="password" name="confirm_password" required placeholder="Repeat your password"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                </div>

                <button type="submit"
                    class="w-full h-11 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-md flex items-center justify-center space-x-2">
                    <span>Register Account</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>

            <div class="pt-4 border-t border-gray-100 text-center text-xs text-gray-600">
                <span>Already have an account?</span>
                <a href="<?= url('login') ?>" class="font-semibold text-red-600 hover:underline ml-1">Sign In</a>
            </div>

        </div>

    </div>
</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>