<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | ImportWala B2B Wholesale</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-[#f8fafc] text-slate-900 font-sans min-h-screen flex flex-col items-center justify-center p-4 sm:p-6">

    <div class="max-w-md w-full space-y-6">

        <!-- Top Header Logo & Title -->
        <div class="text-center space-y-2">
            <div class="flex items-center justify-center space-x-2">
                <img src="<?= url('assets/images/importwale-logo.png') ?>" alt="ImportWala"
                    class="h-10 w-auto object-contain">
            </div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">B2B Wholesale Admin Portal</p>
        </div>

        <!-- Clean Form Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-8 shadow-sm space-y-6">

            <!-- Card Header -->
            <div>
                <h2 class="text-2xl font-semibold text-slate-900 tracking-tight">Sign In</h2>
                <p class="text-xs text-slate-500 font-medium mt-1">Sign in to your account to continue</p>
            </div>

            <!-- Flash Notifications -->
            <?php if ($flash = (new App\Core\Session())->getFlash('error')): ?>
                <div
                    class="bg-rose-50 border border-rose-200 text-rose-700 text-xs p-3.5 rounded-xl font-medium flex items-center space-x-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                    <span><?= htmlspecialchars($flash) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($flash = (new App\Core\Session())->getFlash('success')): ?>
                <div
                    class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs p-3.5 rounded-xl font-medium flex items-center space-x-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span><?= htmlspecialchars($flash) ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="<?= url('admin/login') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <!-- Username Field -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Username or Email</label>
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <input type="text" name="username" required placeholder="Enter your username or email"
                            class="w-full h-11 pl-10 pr-4 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#f05a29] focus:ring-2 focus:ring-[#f05a29]/15 transition">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password</label>
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input type="password" id="adminPassword" name="password" required
                            placeholder="Enter your password"
                            class="w-full h-11 pl-10 pr-11 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-[#f05a29] focus:ring-2 focus:ring-[#f05a29]/15 transition">
                        <button type="button" onclick="togglePassword('adminPassword', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none p-1 rounded-lg hover:bg-slate-100 transition"
                            title="Toggle password visibility">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full h-11 bg-[#f05a29] hover:bg-[#d8481b] text-white font-semibold text-sm rounded-xl transition shadow-xs flex items-center justify-center cursor-pointer">
                    Sign In
                </button>

                <!-- Forgot Password Link -->
                <div class="text-center pt-2">
                    <a href="<?= url('admin/forgot-password') ?>"
                        class="text-xs font-medium text-slate-500 hover:text-[#f05a29] transition">
                        Forgot password?
                    </a>
                </div>
            </form>

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
        lucide.createIcons();
    </script>
</body>

</html>