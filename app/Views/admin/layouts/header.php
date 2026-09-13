<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <script>
        window.CSRF_TOKEN = "<?= csrf_token() ?>";
        var CSRF_TOKEN = window.CSRF_TOKEN;
    </script>
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
            background-color: #f8fafc;
            color: #0f172a;
        }

        .dark body {
            background-color: #0f172a;
            color: #f8fafc;
        }

        /* Custom Sleek Grey Vertical Scrollbar for Admin Panel Right Corner */
        ::-webkit-scrollbar {
            width: 7px !important;
            height: 7px !important;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9 !important;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1 !important;
            border-radius: 9999px !important;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8 !important;
        }
        * {
            scrollbar-width: thin !important;
            scrollbar-color: #cbd5e1 #f1f5f9 !important;
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

        /* Primary Brand Accent Definitions */
        .bg-brand-primary {
            background-color: #f05a29 !important;
        }
        .text-brand-primary {
            color: #f05a29 !important;
        }
        .border-brand-primary {
            border-color: #f05a29 !important;
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

<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans antialiased flex h-screen overflow-hidden">

    <!-- Fixed Sidebar Partial -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Fixed Header & Scrollable Main Content -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">

        <!-- Fixed Top Header with Theme Switcher -->
        <header
            class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-6 py-3.5 flex items-center justify-between shadow-2xs shrink-0 sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <div class="w-2.5 h-2.5 rounded-full bg-[#f05a29] animate-pulse"></div>
                <h1 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2">
                    Wholesale Admin Portal
                </h1>
                <span class="text-slate-300 dark:text-slate-700 text-xs">|</span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">B2B Import Platform</span>
            </div>

            <div class="flex items-center space-x-3">
                <!-- Storefront Quick Link -->
                <a href="<?= url('/') ?>" target="_blank"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs flex items-center space-x-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                    title="View Storefront Website">
                    <i data-lucide="globe" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span class="hidden sm:inline">View Website</span>
                </a>

                <!-- Theme Toggle Button -->
                <button type="button" onclick="toggleMudsorTheme()"
                    class="p-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition"
                    title="Toggle Light/Dark Theme">
                    <i data-lucide="sun" class="w-4 h-4 hidden dark:block text-amber-400"></i>
                    <i data-lucide="moon" class="w-4 h-4 block dark:hidden text-slate-600"></i>
                </button>

                <!-- Admin Logout Header Button -->
                <a href="<?= url('admin/logout') ?>"
                    onclick="openLogoutModal('<?= url('admin/logout') ?>'); return false;"
                    class="px-3.5 py-1.5 rounded-xl border border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 font-semibold text-xs flex items-center space-x-1.5 hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white transition shadow-2xs"
                    title="Log Out of Admin Panel">
                    <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                    <span>Log Out</span>
                </a>
            </div>
        </header>

        <!-- Scrollable Main Content Container -->
        <main class="flex-1 p-6 overflow-y-auto bg-slate-100/70 dark:bg-slate-950">
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