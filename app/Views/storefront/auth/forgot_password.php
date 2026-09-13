<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="bg-gradient-to-b from-[#FFF2ED] via-[#FFF9F6] to-[#FFF2ED] dark:from-[#111827] dark:via-[#182030] dark:to-[#111827] py-10 sm:py-16 min-h-[80vh] flex items-center justify-center font-sans border-b border-gray-200/60 dark:border-gray-800 transition-colors">
    <div class="container mx-auto px-4 max-w-4xl">

        <!-- Elevated Dual-Panel Split Auth Container -->
        <div class="bg-white dark:bg-[#1f2937] rounded-3xl border border-[#f05a29]/20 dark:border-gray-700 shadow-2xl shadow-[#f05a29]/10 overflow-hidden grid grid-cols-1 md:grid-cols-12 min-h-[500px]">
            
            <!-- Left Side: Brand Hero & Security Notice -->
            <div class="md:col-span-5 bg-gradient-to-br from-[#f05a29] via-[#d8481b] to-[#111827] p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
                <!-- Background Glowing Accents -->
                <div class="absolute -top-16 -left-16 w-48 h-48 bg-orange-400/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-16 -right-16 w-48 h-48 bg-amber-400/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 space-y-6">
                    <div class="inline-flex items-center space-x-2 bg-white/15 backdrop-blur-md px-3 py-1.5 rounded-full text-xs font-semibold text-white border border-white/20 shadow-xs">
                        <i data-lucide="key-round" class="w-3.5 h-3.5 text-amber-300"></i>
                        <span>Account Recovery</span>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-2xl font-black text-white leading-tight">Forgot Your Password?</h2>
                        <p class="text-xs text-orange-100/90 leading-relaxed">Don't worry! Enter your registered account email and we'll help you reset it securely.</p>
                    </div>

                    <div class="space-y-3.5 pt-2">
                        <div class="flex items-start space-x-3">
                            <div class="w-7 h-7 rounded-lg bg-white/15 text-amber-300 flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white">256-bit Encrypted Token</h4>
                                <p class="text-[11px] text-orange-100/80">Secure password recovery protocol</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="w-7 h-7 rounded-lg bg-white/15 text-emerald-300 flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                                <i data-lucide="mail-check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white">Instant Reset Link</h4>
                                <p class="text-[11px] text-orange-100/80">Delivered straight to your inbox</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 pt-6 mt-6 border-t border-white/15 text-[11px] text-orange-100/70">
                    <span>ImportWale B2B Wholesale Customer Service</span>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="md:col-span-7 p-8 sm:p-12 flex flex-col justify-center space-y-6">
                
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-[#fff2ed] dark:bg-orange-950/40 text-[#f05a29] dark:text-orange-400 border border-[#f05a29]/20 flex items-center justify-center shrink-0 shadow-xs">
                        <i data-lucide="key-round" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Forgot Password</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Enter your email address below to receive password reset instructions.</p>
                    </div>
                </div>

                <?php 
                $displayError = $error ?? (new App\Core\Session())->getFlash('error');
                $displaySuccess = $success ?? (new App\Core\Session())->getFlash('success');
                $displayResetLink = $reset_link ?? (new App\Core\Session())->getFlash('reset_link');
                ?>

                <?php if (!empty($displayError)): ?>
                    <div class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/60 text-red-700 dark:text-red-300 text-xs font-medium p-3.5 rounded-xl flex items-center space-x-2.5 shadow-xs">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-[#f05a29] dark:text-red-400 shrink-0"></i>
                        <span><?= htmlspecialchars($displayError) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($displaySuccess)): ?>
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-emerald-700 dark:text-emerald-300 text-xs font-medium p-3.5 rounded-xl flex items-center space-x-2.5 shadow-xs">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                        <span><?= htmlspecialchars($displaySuccess) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($displayResetLink)): ?>
                    <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 text-blue-800 dark:text-blue-300 text-xs p-4 rounded-xl space-y-2">
                        <div class="font-bold flex items-center space-x-1.5 text-blue-900 dark:text-blue-200">
                            <i data-lucide="key" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                            <span>Reset Link Generated:</span>
                        </div>
                        <p class="text-[11px] text-blue-700 dark:text-blue-300">Click below to proceed to password reset page:</p>
                        <a href="<?= htmlspecialchars($displayResetLink) ?>"
                            class="inline-block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-3 rounded-xl transition text-xs shadow-xs">
                            Reset Account Password &rarr;
                        </a>
                    </div>
                <?php endif; ?>

                <form action="<?= url('forgot-password') ?>" method="POST" class="space-y-4">
                    <?= csrf_field() ?>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1.5">Email Address</label>
                        <div class="relative">
                            <i data-lucide="mail" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="email" name="email" required placeholder="you@example.com"
                                class="w-full h-12 pl-10 pr-4 bg-gray-50 dark:bg-gray-800/80 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-medium text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-[#f05a29] focus:bg-white dark:focus:bg-gray-800 focus:ring-4 focus:ring-[#f05a29]/15 transition-all">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full h-12 bg-gradient-to-r from-[#f05a29] to-[#d8481b] hover:from-[#d8481b] hover:to-[#b83812] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-lg shadow-[#f05a29]/25 hover:shadow-[#f05a29]/40 flex items-center justify-center space-x-2 active:scale-[0.99] cursor-pointer">
                        <span>Send Reset Link</span>
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </button>
                </form>

                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 text-center text-xs text-gray-600 dark:text-gray-400">
                    <a href="<?= url('login') ?>"
                        class="font-bold text-[#f05a29] dark:text-orange-400 hover:underline inline-flex items-center justify-center space-x-1.5 transition-colors">
                        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        <span>Back to Customer Login</span>
                    </a>
                </div>

            </div>

        </div>

    </div>
</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>