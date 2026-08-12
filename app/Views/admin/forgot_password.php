<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Mudsor Admin Portal</title>

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
            <img src="<?= asset('images/mudsor-logo.png') ?>" alt="Mudsor Logo" class="h-10 mx-auto object-contain"
                onerror="this.onerror=null; this.src='https://via.placeholder.com/140x40?text=MUDSOR';">
            <h2 class="text-xl font-semibold text-gray-900">Forgot Password</h2>
            <p class="text-xs text-gray-500">Enter your registered username or email to reset password</p>
        </div>

        <?php 
        $displayError = $error ?? (new App\Core\Session())->getFlash('error');
        $displaySuccess = $success ?? (new App\Core\Session())->getFlash('success');
        $displayResetLink = $reset_link ?? (new App\Core\Session())->getFlash('reset_link');
        ?>

        <?php if (!empty($displayError)): ?>
            <div
                class="bg-red-50 border border-red-200 text-red-700 text-xs p-3.5 rounded-[10px] font-semibold flex items-center space-x-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
                <span><?= htmlspecialchars($displayError) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($displaySuccess)): ?>
            <div
                class="bg-green-50 border border-green-200 text-green-700 text-xs p-3.5 rounded-[10px] font-semibold flex items-center space-x-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-green-600 shrink-0"></i>
                <span><?= htmlspecialchars($displaySuccess) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($displayResetLink)): ?>
            <div class="bg-blue-50 border border-blue-200 text-blue-800 text-xs p-4 rounded-[10px] space-y-2">
                <div class="font-semibold flex items-center space-x-1.5 text-blue-900">
                    <i data-lucide="key" class="w-4 h-4 text-blue-600"></i>
                    <span>Password Reset Link Generated:</span>
                </div>
                <p class="text-[11px] text-blue-700">Click the button below to set a new password:</p>
                <a href="<?= htmlspecialchars($displayResetLink) ?>"
                    class="inline-block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-3 rounded-[8px] transition text-xs shadow-sm">
                    Reset Password Now &rarr;
                </a>
            </div>
        <?php endif; ?>

        <form action="<?= url('admin/forgot-password') ?>" method="POST" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Username or
                    Email</label>
                <input type="text" name="username" required placeholder="Enter username or email"
                    class="w-full h-12 px-4 bg-gray-50 border border-gray-900 rounded-[10px] text-xs font-semibold text-gray-900 focus:outline-none focus:border-gray-900 transition">
            </div>

            <button type="submit"
                class="w-full h-12 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-[10px] transition shadow-md flex items-center justify-center space-x-2">
                <span>Send Password Reset Request</span>
                <i data-lucide="send" class="w-4 h-4"></i>
            </button>
        </form>

        <div class="pt-4 border-t border-gray-100 text-center text-xs">
            <a href="<?= url('admin/login') ?>"
                class="font-semibold text-gray-600 hover:text-gray-900 inline-flex items-center space-x-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Back to Admin Login</span>
            </a>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>