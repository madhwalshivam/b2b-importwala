<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="bg-gradient-to-b from-[#FFF2ED] via-[#FFF9F6] to-[#FFF2ED] dark:from-[#111827] dark:via-[#182030] dark:to-[#111827] py-10 sm:py-16 min-h-[80vh] flex items-center justify-center font-sans border-b border-gray-200/60 dark:border-gray-800 transition-colors">
    <div class="container mx-auto px-4 max-w-4xl">

        <!-- Elevated Dual-Panel Split Auth Container -->
        <div class="bg-white dark:bg-[#1f2937] rounded-3xl border border-[#f05a29]/20 dark:border-gray-700 shadow-2xl shadow-[#f05a29]/10 overflow-hidden grid grid-cols-1 md:grid-cols-12 min-h-[580px]">
            
            <!-- Left Side: Brand Hero & B2B Wholesale Perks -->
            <div class="md:col-span-5 bg-gradient-to-br from-[#f05a29] via-[#d8481b] to-[#111827] p-8 sm:p-10 text-white flex flex-col justify-between relative overflow-hidden">
                <!-- Background Glowing Accents -->
                <div class="absolute -top-16 -left-16 w-48 h-48 bg-orange-400/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-16 -right-16 w-48 h-48 bg-amber-400/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 space-y-6">
                    <div class="inline-flex items-center space-x-2 bg-white/15 backdrop-blur-md px-3 py-1.5 rounded-full text-xs font-semibold text-white border border-white/20 shadow-xs">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5 text-amber-300"></i>
                        <span>Join ImportWale B2B</span>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-2xl font-black text-white leading-tight">Create Dealer & Customer Account</h2>
                        <p class="text-xs text-orange-100/90 leading-relaxed">Sign up today to access bulk B2B pricing, guaranteed fitment EV parts, and express order management.</p>
                    </div>

                    <!-- Benefit Bullet Points -->
                    <div class="space-y-3.5 pt-2">
                        <div class="flex items-start space-x-3">
                            <div class="w-7 h-7 rounded-lg bg-white/15 text-amber-300 flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                                <i data-lucide="percent" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white">Wholesale Discounts</h4>
                                <p class="text-[11px] text-orange-100/80">Direct factory pricing for bulk buyers</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="w-7 h-7 rounded-lg bg-white/15 text-emerald-300 flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                                <i data-lucide="shield-check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white">Verified OEM Accessories</h4>
                                <p class="text-[11px] text-orange-100/80">Premium EV crash guards & accessories</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-3">
                            <div class="w-7 h-7 rounded-lg bg-white/15 text-sky-300 flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-white">Instant Order Updates</h4>
                                <p class="text-[11px] text-orange-100/80">Real-time SMS & email notifications</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 pt-6 mt-6 border-t border-white/15 text-[11px] text-orange-100/70">
                    <span>Trusted by 5,000+ EV Dealers & Mechanics</span>
                </div>
            </div>

            <!-- Right Side: Signup Form -->
            <div class="md:col-span-7 p-8 sm:p-12 flex flex-col justify-center space-y-5">
                
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-[#fff2ed] dark:bg-orange-950/40 text-[#f05a29] dark:text-orange-400 border border-[#f05a29]/20 flex items-center justify-center shrink-0 shadow-xs">
                        <i data-lucide="user-plus" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Create Account</h1>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Fill in your details below to register your account.</p>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800/60 text-red-700 dark:text-red-300 text-xs font-medium p-3.5 rounded-xl flex items-center space-x-2.5 shadow-xs">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-[#f05a29] dark:text-red-400 shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form action="<?= url('signup') ?>" method="POST" class="space-y-3.5">
                    <?= csrf_field() ?>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Full Name</label>
                        <div class="relative">
                            <i data-lucide="user" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="text" name="name" required placeholder="John Doe"
                                class="w-full h-11 pl-10 pr-4 bg-gray-50 dark:bg-gray-800/80 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-medium text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-[#f05a29] focus:bg-white dark:focus:bg-gray-800 focus:ring-4 focus:ring-[#f05a29]/15 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Email Address</label>
                        <div class="relative">
                            <i data-lucide="mail" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="email" name="email" required placeholder="you@example.com"
                                class="w-full h-11 pl-10 pr-4 bg-gray-50 dark:bg-gray-800/80 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-medium text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-[#f05a29] focus:bg-white dark:focus:bg-gray-800 focus:ring-4 focus:ring-[#f05a29]/15 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Phone Number (Optional)</label>
                        <div class="relative">
                            <i data-lucide="phone" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                            <input type="tel" name="phone" placeholder="+91 98765 43210"
                                class="w-full h-11 pl-10 pr-4 bg-gray-50 dark:bg-gray-800/80 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-medium text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-[#f05a29] focus:bg-white dark:focus:bg-gray-800 focus:ring-4 focus:ring-[#f05a29]/15 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Password</label>
                            <div class="relative">
                                <i data-lucide="lock" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                <input type="password" id="signupPassword" name="password" required placeholder="Min 6 chars"
                                    class="w-full h-11 pl-10 pr-10 bg-gray-50 dark:bg-gray-800/80 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-medium text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-[#f05a29] focus:bg-white dark:focus:bg-gray-800 focus:ring-4 focus:ring-[#f05a29]/15 transition-all">
                                <button type="button" onclick="togglePassword('signupPassword', this)"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none p-1 rounded-lg transition"
                                    title="Toggle password visibility">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">Confirm Password</label>
                            <div class="relative">
                                <i data-lucide="shield-check" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                <input type="password" id="signupConfirmPassword" name="confirm_password" required placeholder="Repeat password"
                                    class="w-full h-11 pl-10 pr-10 bg-gray-50 dark:bg-gray-800/80 border border-gray-300 dark:border-gray-600 rounded-xl text-xs font-medium text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:border-[#f05a29] focus:bg-white dark:focus:bg-gray-800 focus:ring-4 focus:ring-[#f05a29]/15 transition-all">
                                <button type="button" onclick="togglePassword('signupConfirmPassword', this)"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none p-1 rounded-lg transition"
                                    title="Toggle password visibility">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full h-12 bg-gradient-to-r from-[#f05a29] to-[#d8481b] hover:from-[#d8481b] hover:to-[#b83812] text-white font-bold text-xs uppercase tracking-wider rounded-xl transition-all shadow-lg shadow-[#f05a29]/25 hover:shadow-[#f05a29]/40 flex items-center justify-center space-x-2 active:scale-[0.99] cursor-pointer mt-2">
                        <span>Register Account</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <div class="pt-3 border-t border-gray-100 dark:border-gray-800 text-center text-xs text-gray-600 dark:text-gray-400">
                    <span>Already have an account?</span>
                    <a href="<?= url('login') ?>" class="font-bold text-[#f05a29] dark:text-orange-400 hover:underline ml-1">Sign In</a>
                </div>

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
        btn.innerHTML = `<i data-lucide="${isPassword ? 'eye-off' : 'eye'}" class="w-3.5 h-3.5"></i>`;
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