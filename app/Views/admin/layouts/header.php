<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ImportWala Admin Panel</title>

    <!-- Central Theme Design Tokens & Fonts -->
    <link rel="stylesheet" href="<?= asset('assets/css/everful-theme.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/theme.css') ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        theme: {
                            primary: '#f05a29',
                            'primary-dark': '#d8481b',
                            secondary: '#111827',
                            accent: '#f05a29',
                            bg: '#ffffff',
                            'bg-soft': '#f9fafb',
                            text: '#111827',
                            'text-muted': '#6b7280',
                            success: '#10b981',
                            warning: '#f59e0b',
                            danger: '#ef4444',
                            gold: '#f59e0b'
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style id="importwala-brand-overrides">
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--color-bg-soft, #f9fafb);
            color: var(--color-text, #111827);
        }

        .line-clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        html:not(.dark) .bg-red-50 {
            background-color: rgba(240, 90, 41, 0.08) !important;
        }

        html:not(.dark) .bg-red-100 {
            background-color: rgba(240, 90, 41, 0.12) !important;
        }

        html:not(.dark) .bg-red-200 {
            background-color: rgba(240, 90, 41, 0.20) !important;
        }

        .bg-red-500,
        .bg-red-600 {
            background-color: #f05a29 !important;
        }

        .bg-red-700,
        .bg-red-800 {
            background-color: #d8481b !important;
        }

        .hover\:bg-red-600:hover {
            background-color: #f05a29 !important;
        }

        .hover\:bg-red-700:hover {
            background-color: #d8481b !important;
        }

        .text-red-400 {
            color: rgba(240, 90, 41, 0.8) !important;
        }

        .text-red-500,
        .text-red-600 {
            color: #f05a29 !important;
        }

        .text-red-700 {
            color: #d8481b !important;
        }

        .hover\:text-red-600:hover {
            color: #f05a29 !important;
        }

        .hover\:text-red-700:hover {
            color: #d8481b !important;
        }

        .border-red-500,
        .border-red-600 {
            border-color: #f05a29 !important;
        }

        .border-red-700 {
            border-color: #d8481b !important;
        }

        .hover\:border-red-600:hover {
            border-color: #f05a29 !important;
        }

        .focus\:border-red-600:focus {
            border-color: #f05a29 !important;
        }

        .focus\:ring-red-500:focus,
        .focus\:ring-red-600:focus {
            --tw-ring-color: #f05a29 !important;
        }

        .fill-red-500,
        .fill-red-600 {
            fill: #f05a29 !important;
        }

        .text-amber-400,
        .text-amber-500 {
            color: #f05a29 !important;
        }

        .fill-amber-400 {
            fill: #f05a29 !important;
        }

        .bg-purple-600,
        .bg-indigo-600 {
            background-color: #f05a29 !important;
        }

        .hover\:bg-purple-700:hover,
        .hover\:bg-indigo-700:hover {
            background-color: #d8481b !important;
        }

        .text-purple-600,
        .text-indigo-600 {
            color: #f05a29 !important;
        }
    </style>

    <!-- Theme Toggle & Auto-Restore Script -->
    <script>
        function applyMudsorTheme(theme) {
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.body.classList.add('dark-theme');
                localStorage.setItem('mudsor_theme', 'dark');
                const lbl = document.getElementById('theme-btn-label');
                if (lbl) lbl.innerText = 'Light Mode';
            } else {
                document.documentElement.classList.remove('dark');
                document.body.classList.remove('dark-theme');
                localStorage.setItem('mudsor_theme', 'light');
                const lbl = document.getElementById('theme-btn-label');
                if (lbl) lbl.innerText = 'Dark Mode';
            }
        }

        function toggleMudsorTheme() {
            const current = localStorage.getItem('mudsor_theme') || 'light';
            applyMudsorTheme(current === 'dark' ? 'light' : 'dark');
        }

        // Auto-restore on load
        (function () {
            const saved = localStorage.getItem('mudsor_theme') || 'light';
            if (saved === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
        document.addEventListener('DOMContentLoaded', () => {
            const saved = localStorage.getItem('mudsor_theme') || 'light';
            applyMudsorTheme(saved);
        });
    </script>

    <!-- Central Toast & Logout Modal Systems -->
    <script src="<?= asset('js/toast.js') ?>"></script>
    <script src="<?= asset('js/logout-modal.js') ?>"></script>
</head>

<body class="bg-gray-100 text-gray-900 font-sans antialiased flex h-screen overflow-hidden">

    <!-- Fixed Sidebar Partial -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Fixed Header & Scrollable Main Content -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

        <!-- Fixed Top Header with Theme Switcher -->
        <header
            class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-xs shrink-0 sticky top-0 z-30">
            <h1 class="text-base font-semibold text-gray-900 tracking-tight flex items-center gap-2">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-[#f05a29]"></span>
                Wholesale Admin Panel
            </h1>

            <div class="flex items-center space-x-3">
                <button type="button" onclick="toggleMudsorTheme()"
                    class="px-3.5 py-1.5 rounded-xl border border-gray-900 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-700 dark:text-neutral-200 font-semibold text-xs flex items-center space-x-2 shadow-2xs hover:bg-gray-100 dark:hover:bg-neutral-700 transition cursor-pointer"
                    title="Toggle Light / Dark Mode">
                    <i data-lucide="sun" class="w-4 h-4 text-amber-500 dark:hidden"></i>
                    <i data-lucide="moon" class="w-4 h-4 text-amber-400 hidden dark:inline-block"></i>
                    <span id="theme-btn-label">Light Mode</span>
                </button>

                <!-- Admin Logout Header Button -->
                <a href="<?= url('admin/logout') ?>"
                    onclick="openLogoutModal('<?= url('admin/logout') ?>'); return false;"
                    class="px-3.5 py-1.5 rounded-xl border border-red-200 bg-red-50 text-red-700 font-semibold text-xs flex items-center space-x-1.5 hover:bg-red-600 hover:text-white transition shadow-2xs"
                    title="Log Out of Admin Panel">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                    <span>Log Out</span>
                </a>
            </div>
        </header>

        <!-- Scrollable Main Content Container -->
        <main class="flex-1 p-6 overflow-y-auto" style="background-color: var(--color-bg-soft);">
            <?php if ($flash = (new App\Core\Session())->getFlash('success')): ?>
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 1000)"
                    x-transition:enter="transition ease-out duration-200 transform"
                    x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-300 transform"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                    class="bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs p-3.5 px-4 rounded-xl font-semibold mb-6 flex items-center justify-between shadow-xs transition-all">
                    <div class="flex items-center space-x-2.5">
                        <div
                            class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <i data-lucide="check" class="w-3.5 h-3.5 stroke-[3]"></i>
                        </div>
                        <span class="leading-snug"><?= htmlspecialchars($flash) ?></span>
                    </div>
                    <button type="button" @click="show = false"
                        class="text-emerald-500 hover:text-emerald-800 dark:hover:text-emerald-200 p-1.5 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 rounded-lg transition focus:outline-none shrink-0"
                        title="Close Notification">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($flash = (new App\Core\Session())->getFlash('error')): ?>
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2500)"
                    x-transition:enter="transition ease-out duration-200 transform"
                    x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-300 transform"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                    class="bg-rose-50 dark:bg-rose-950/50 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs p-3.5 px-4 rounded-xl font-semibold mb-6 flex items-center justify-between shadow-xs transition-all">
                    <div class="flex items-center space-x-2.5">
                        <div
                            class="w-6 h-6 rounded-full bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5 stroke-[2.5]"></i>
                        </div>
                        <span class="leading-snug"><?= htmlspecialchars($flash) ?></span>
                    </div>
                    <button type="button" @click="show = false"
                        class="text-rose-500 hover:text-rose-800 dark:hover:text-rose-200 p-1.5 hover:bg-rose-100 dark:hover:bg-rose-900/50 rounded-lg transition focus:outline-none shrink-0"
                        title="Close Notification">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            <?php endif; ?>