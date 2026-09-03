<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | ImportWale Admin Portal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-gray-900 text-gray-900 font-sans min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white border border-gray-900 rounded-[10px] p-8 shadow-2xl space-y-6">

        <div class="text-center space-y-2">
            <img src="<?= asset('images/importwale-logo.png') ?>" alt="ImportWale Logo" class="h-10 mx-auto object-contain"
                onerror="this.onerror=null; this.src='https://via.placeholder.com/140x40?text=IMPORTWALE';">
            <h2 class="text-xl font-semibold text-gray-900">Set New Password</h2>
            <p class="text-xs text-gray-500">Create a new secure password for your admin account</p>
        </div>

        <?php if (!empty($error)): ?>
            <div
                class="bg-red-50 border border-red-200 text-red-700 text-xs p-3.5 rounded-[10px] font-semibold flex items-center space-x-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($tokenValid)): ?>
            <form action="<?= url('admin/reset-password') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">New
                        Password</label>
                    <div class="relative">
                        <input type="password" id="adminNewPassword" name="password" required
                            placeholder="Minimum 6 characters"
                            class="w-full h-12 pl-4 pr-12 bg-gray-50 border border-gray-900 rounded-[10px] text-xs font-semibold text-gray-900 focus:outline-none focus:border-gray-900 transition">
                        <button type="button" onclick="togglePassword('adminNewPassword', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none p-1.5 rounded-md hover:bg-gray-100 transition"
                            title="Toggle password visibility">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Confirm New
                        Password</label>
                    <div class="relative">
                        <input type="password" id="adminConfirmPassword" name="confirm_password" required
                            placeholder="Re-enter new password"
                            class="w-full h-12 pl-4 pr-12 bg-gray-50 border border-gray-900 rounded-[10px] text-xs font-semibold text-gray-900 focus:outline-none focus:border-gray-900 transition">
                        <button type="button" onclick="togglePassword('adminConfirmPassword', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none p-1.5 rounded-md hover:bg-gray-100 transition"
                            title="Toggle password visibility">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full h-12 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-[10px] transition shadow-md flex items-center justify-center space-x-2">
                    <span>Update & Save Password</span>
                    <i data-lucide="check" class="w-4 h-4"></i>
                </button>
            </form>
        <?php else: ?>
            <div class="text-center space-y-4 py-4">
                <p class="text-xs text-red-600 font-semibold">The password reset link is invalid or has expired.</p>
                <a href="<?= url('admin/forgot-password') ?>"
                    class="inline-block bg-gray-900 hover:bg-black text-white font-semibold text-xs py-2.5 px-4 rounded-[10px] transition">
                    Request New Reset Link
                </a>
            </div>
        <?php endif; ?>

        <div class="pt-4 border-t border-gray-100 text-center text-xs">
            <a href="<?= url('admin/login') ?>"
                class="font-semibold text-gray-600 hover:text-gray-900 inline-flex items-center space-x-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Back to Admin Login</span>
            </a>
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