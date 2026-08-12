<?php
use App\Helpers\SEO;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ScooterModel;
use App\Core\Database;

$brandModel = new Brand();
$categoryModel = new Category();
$scooterModel = new ScooterModel();

$navBrands = $brandModel->getActiveBrands();
$navCategories = $categoryModel->getActiveCategories();
$navModels = $scooterModel->getAllWithBrand();

// Fetch active announcement if not already passed
if (!isset($announcement)) {
    $db = Database::getInstance();
    $announcementStmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    $announcement = $announcementStmt->fetch();
}

// Fetch social media settings from DB in ONE query (avoids namespace issues)
$db = Database::getInstance();
$_socialKeys = [
    'social_instagram',
    'social_youtube',
    'social_facebook',
    'social_twitter',
    'social_whatsapp',
    'social_linkedin',
    'show_social_instagram',
    'show_social_youtube',
    'show_social_facebook',
    'show_social_twitter',
    'show_social_whatsapp',
    'show_social_linkedin',
];
$_placeholders = implode(',', array_fill(0, count($_socialKeys), '?'));
$_socialStmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ($_placeholders)");
$_socialStmt->execute($_socialKeys);
$socialSettings = [];
foreach ($_socialStmt->fetchAll(\PDO::FETCH_ASSOC) as $_row) {
    $socialSettings[$_row['setting_key']] = $_row['setting_value'];
}
unset($_socialKeys, $_placeholders, $_socialStmt, $_row);

$wishlistCount = 0;
$wishlistProductIds = [];
if (!empty($_SESSION['user_id'])) {
    $db = Database::getInstance();
    $wStmt = $db->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $wStmt->execute([$_SESSION['user_id']]);
    $wishlistProductIds = array_map('intval', $wStmt->fetchAll(\PDO::FETCH_COLUMN));
    $wishlistCount = count($wishlistProductIds);
} else {
    $wishlistProductIds = array_map('intval', $_SESSION['guest_wishlist'] ?? []);
    $wishlistCount = count($wishlistProductIds);
}

$cartCount = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $cItem) {
        $cartCount += (int) ($cItem['quantity'] ?? 1);
    }
}
$compareCount = count($_SESSION['compare'] ?? []);

$currentUri = $_SERVER['REQUEST_URI'] ?? '';
$isHome = ($currentUri === '/' || $currentUri === '/ecommerce' || $currentUri === '/ecommerce/' || $currentUri === '/ecommerce/index.php');
$isShop = str_contains($currentUri, 'shop') && !str_contains($currentUri, 'featured=1');
$isCategory = str_contains($currentUri, 'categor');
$isBrand = str_contains($currentUri, 'brand');
$isBestSeller = str_contains($currentUri, 'featured=1');
$isAbout = str_contains($currentUri, 'about-us');
$isContact = str_contains($currentUri, 'contact-us');
$isBlog = str_contains($currentUri, 'blog');
?>
<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= SEO::renderMeta($seoOptions ?? []) ?>

    <!-- Central Theme Design Tokens & Fonts (load first for CSS vars) -->
    <link rel="stylesheet" href="<?= asset('css/theme.css') ?>?v=<?= time() ?>">
    
    <style id="mudsor-scroll-reveal-styles">
        .reveal-on-scroll {
            opacity: 0 !important;
            transform: translateY(50px) !important;
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1) !important;
            will-change: opacity, transform;
        }
        .reveal-on-scroll.is-revealed {
            opacity: 1 !important;
            transform: translateY(0) !important;
        }
        @media (prefers-reduced-motion: reduce) {
            .reveal-on-scroll {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
            }
        }
    </style>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        theme: {
                            primary: 'var(--color-primary)',
                            'primary-dark': 'var(--color-primary-dark)',
                            secondary: 'var(--color-secondary)',
                            accent: 'var(--color-accent)',
                            bg: 'var(--bg-body)',
                            'bg-soft': 'var(--bg-body-soft)',
                            card: 'var(--bg-card)',
                            text: 'var(--color-text)',
                            'text-muted': 'var(--color-text-muted)',
                            success: 'var(--color-success)',
                            warning: 'var(--color-warning)',
                            danger: 'var(--color-danger)',
                            gold: 'var(--color-highlight-gold)'
                        }
                    },
                    boxShadow: {
                        'card': '0 4px 12px rgba(0, 0, 0, 0.05)',
                    }
                }
            }
    </script>

    <!-- Icon Libraries (Font Awesome 6 & Material Icons Outlined) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined">

    <!-- Brand Color Override Block: runs AFTER Tailwind to guarantee brand wins -->
    <!-- This style tag has higher specificity via load order -->
    <style id="mudsor-brand-overrides">
        /* Site-wide light red tint background overrides for canvas & sections */
        html:not(.dark) body {
            background-color: var(--bg-body, #FFF5F4) !important;
        }

        html:not(.dark) .bg-gray-50,
        html:not(.dark) .bg-gray-100,
        html:not(.dark) .bg-slate-50,
        html:not(.dark) .bg-slate-100 {
            background-color: var(--bg-body, #FFF5F4) !important;
        }

        html:not(.dark) .bg-gray-50\/80,
        html:not(.dark) .bg-gray-50\/70,
        html:not(.dark) .bg-gray-50\/60,
        html:not(.dark) .bg-gray-50\/50 {
            background-color: var(--bg-body-soft, #FEF0EF) !important;
        }

        /* Ensure input fields inside forms stay clean white */
        html:not(.dark) input.bg-gray-50,
        html:not(.dark) select.bg-gray-50,
        html:not(.dark) textarea.bg-gray-50 {
            background-color: #FFFFFF !important;
        }

        html:not(.dark) .bg-red-50 {
            background-color: rgba(168, 17, 28, .06) !important
        }

        html:not(.dark) .bg-red-100 {
            background-color: rgba(168, 17, 28, .10) !important
        }

        html:not(.dark) .bg-red-200 {
            background-color: rgba(168, 17, 28, .18) !important
        }

        .bg-red-400 {
            background-color: rgba(168, 17, 28, .75) !important
        }

        .bg-red-500 {
            background-color: #A8111C !important
        }

        .bg-red-600 {
            background-color: #A8111C !important
        }

        .bg-red-700 {
            background-color: #6E0D14 !important
        }

        .bg-red-800 {
            background-color: #6E0D14 !important
        }

        .hover\:bg-red-600:hover {
            background-color: #A8111C !important
        }

        .hover\:bg-red-700:hover {
            background-color: #6E0D14 !important
        }

        .hover\:bg-red-800:hover {
            background-color: #6E0D14 !important
        }

        .text-red-400 {
            color: rgba(168, 17, 28, .70) !important
        }

        .text-red-500 {
            color: #A8111C !important
        }

        .text-red-600 {
            color: #A8111C !important
        }

        .text-red-700 {
            color: #6E0D14 !important
        }

        .hover\:text-red-600:hover {
            color: #A8111C !important
        }

        .hover\:text-red-700:hover {
            color: #6E0D14 !important
        }

        .hover\:text-white:hover {
            color: #ffffff !important
        }

        .border-red-200 {
            border-color: rgba(168, 17, 28, .20) !important
        }

        .border-red-400 {
            border-color: rgba(168, 17, 28, .60) !important
        }

        .border-red-500 {
            border-color: #A8111C !important
        }

        .border-red-600 {
            border-color: #A8111C !important
        }

        .border-red-700 {
            border-color: #6E0D14 !important
        }

        .hover\:border-red-600:hover {
            border-color: #A8111C !important
        }

        .focus\:border-red-600:focus {
            border-color: #A8111C !important
        }

        .focus\:ring-red-500:focus {
            --tw-ring-color: #A8111C !important
        }

        .focus\:ring-red-600:focus {
            --tw-ring-color: #A8111C !important
        }

        .fill-red-400 {
            fill: rgba(168, 17, 28, .70) !important
        }

        .fill-red-500 {
            fill: #A8111C !important
        }

        .fill-red-600 {
            fill: #A8111C !important
        }

        .stroke-red-600 {
            stroke: #A8111C !important
        }

        /* Stars → gold */
        .text-amber-400,
        .text-amber-500 {
            color: #D4A017 !important
        }

        .fill-amber-400 {
            fill: #D4A017 !important
        }

        /* Kill any leftover purple/indigo from old themes */
        .bg-purple-600,
        .bg-indigo-600 {
            background-color: #A8111C !important
        }

        .hover\:bg-purple-700:hover,
        .hover\:bg-indigo-700:hover {
            background-color: #6E0D14 !important
        }

        .text-purple-600,
        .text-indigo-600 {
            color: #A8111C !important
        }

        /* Storefront Dark Mode Comprehensive Styling */
        html.dark body {
            background-color: #0b132b !important;
            color: #ffffff !important;
        }

        html.dark header,
        html.dark nav {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #ffffff !important;
        }

        html.dark .bg-white,
        html.dark .robu-product-card {
            background-color: #1e293b !important;
            border-color: rgba(239, 68, 68, 0.35) !important;
            color: #ffffff !important;
        }

        html.dark .bg-gray-50,
        html.dark .bg-gray-100,
        html.dark .bg-[#EEF2F6] {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }

        html.dark .text-theme-secondary,
        html.dark .text-gray-900,
        html.dark .text-gray-800,
        html.dark .text-gray-700,
        html.dark .text-slate-900,
        html.dark .text-slate-800,
        html.dark .text-slate-700 {
            color: #ffffff !important;
        }

        html.dark .text-gray-600,
        html.dark .text-gray-500,
        html.dark .text-gray-400,
        html.dark .text-slate-600,
        html.dark .text-slate-500,
        html.dark .text-slate-400 {
            color: #cbd5e1 !important;
        }

        html.dark nav a,
        html.dark header a,
        html.dark a {
            color: #ffffff !important;
        }

        html.dark nav a.text-theme-primary,
        html.dark a.text-red-600,
        html.dark a.text-red-500 {
            color: #f87171 !important;
        }

        html.dark .border-gray-100,
        html.dark .border-gray-900,
        html.dark .border-gray-300 {
            border-color: #334155 !important;
        }

        html.dark footer {
            background-color: #020617 !important;
            border-color: #1e293b !important;
        }
    </style>

    <!-- Theme Switcher Script for Storefront -->
    <script>
        function applyStorefrontTheme(theme) {
            const sunIcon = document.getElementById('sf-icon-sun');
            const moonIcon = document.getElementById('sf-icon-moon');
            const lbl = document.getElementById('sf-theme-label');

            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                localStorage.setItem('mudsor_sf_theme', 'dark');
                if (sunIcon) sunIcon.style.setProperty('display', 'none', 'important');
                if (moonIcon) moonIcon.style.setProperty('display', 'inline-block', 'important');
                if (lbl) lbl.innerText = 'Dark';
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('mudsor_sf_theme', 'light');
                if (sunIcon) sunIcon.style.setProperty('display', 'inline-block', 'important');
                if (moonIcon) moonIcon.style.setProperty('display', 'none', 'important');
                if (lbl) lbl.innerText = 'Light';
            }
        }

        function toggleStorefrontTheme() {
            const current = localStorage.getItem('mudsor_sf_theme') || 'light';
            applyStorefrontTheme(current === 'dark' ? 'light' : 'dark');
        }

        (function () {
            const saved = localStorage.getItem('mudsor_sf_theme') || 'light';
            if (saved === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
        document.addEventListener('DOMContentLoaded', () => {
            const saved = localStorage.getItem('mudsor_sf_theme') || 'light';
            applyStorefrontTheme(saved);
        });
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* ── Mobile Sidebar: body scroll lock ── */
        body.overflow-hidden {
            overflow: hidden !important;
            position: fixed;
            width: 100%;
        }

        /* ── Sidebar dark mode ── */
        html.dark [role="dialog"].md\\:hidden {
            background-color: #1e293b !important;
        }

        html.dark [role="dialog"].md\\:hidden .bg-white {
            background-color: #1e293b !important;
        }

        html.dark [role="dialog"].md\\:hidden .bg-gray-50,
        html.dark [role="dialog"].md\\:hidden .bg-gray-50\\/70 {
            background-color: #0f172a !important;
        }

        html.dark [role="dialog"].md\\:hidden .text-gray-800,
        html.dark [role="dialog"].md\\:hidden .text-gray-700,
        html.dark [role="dialog"].md\\:hidden .text-gray-900 {
            color: #f1f5f9 !important;
        }

        html.dark [role="dialog"].md\\:hidden .text-gray-500 {
            color: #94a3b8 !important;
        }

        html.dark [role="dialog"].md\\:hidden .border-gray-100,
        html.dark [role="dialog"].md\\:hidden .border-gray-900 {
            border-color: #334155 !important;
        }

        html.dark [role="dialog"].md\\:hidden a:hover,
        html.dark [role="dialog"].md\\:hidden button:hover {
            background-color: rgba(168, 17, 28, .12) !important;
        }

        html.dark [role="dialog"].md\\:hidden .bg-gray-100,
        html.dark [role="dialog"].md\\:hidden .bg-gray-900 {
            background-color: #334155 !important;
            color: #f1f5f9 !important;
        }

        /* ── Global Mobile Overflow Prevention ── */
        html,
        body {
            overflow-x: hidden;
            max-width: 100%;
        }

        /* ── Hero Banner: Full Mobile Fix ── */
        @media (max-width: 767px) {

            .mobile-hero-section,
            .mobile-hero-section>div,
            .mobile-hero-container {
                width: 100% !important;
                max-width: 100vw !important;
                overflow: hidden !important;
                box-sizing: border-box !important;
            }

            .mobile-hero-container picture,
            .mobile-hero-container img,
            .mobile-hero-container a {
                width: 100% !important;
                max-width: 100% !important;
                display: block !important;
                height: auto !important;
            }

            /* ── Hide dot indicators on mobile ── */
            .mobile-slider-dots {
                display: none !important;
            }
        }

        /* ── Swiper Anti-FOUC & Zero CLS Engine (Zero Layout Shift on Page Load/Refresh) ── */
        .swiper:not(.swiper-initialized) .swiper-wrapper {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: hidden !important;
        }

        /* 1. Categories Swiper (Mobile 2, 481px 2, 768px 5.2, 1025px 6) */
        .swiper-categories:not(.swiper-initialized) .swiper-slide {
            flex: 0 0 calc(50% - 6px) !important;
            width: calc(50% - 6px) !important;
            max-width: calc(50% - 6px) !important;
        }

        @media (min-width: 481px) {
            .swiper-categories:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(50% - 7px) !important;
                width: calc(50% - 7px) !important;
                max-width: calc(50% - 7px) !important;
            }
        }

        @media (min-width: 768px) {
            .swiper-categories:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(19.23% - 11.5px) !important;
                width: calc(19.23% - 11.5px) !important;
                max-width: calc(19.23% - 11.5px) !important;
            }
        }

        @media (min-width: 1025px) {
            .swiper-categories:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(16.66% - 13.3px) !important;
                width: calc(16.66% - 13.3px) !important;
                max-width: calc(16.66% - 13.3px) !important;
            }
        }

        /* 2. Best Sellers & New Arrivals Swiper (Mobile 2, 481px 2, 768px 3.2, 1025px 4, 1441px 5) */
        .swiper-bestsellers:not(.swiper-initialized) .swiper-slide,
        .swiper-newarrivals:not(.swiper-initialized) .swiper-slide {
            flex: 0 0 calc(50% - 6px) !important;
            width: calc(50% - 6px) !important;
            max-width: calc(50% - 6px) !important;
        }

        @media (min-width: 481px) {

            .swiper-bestsellers:not(.swiper-initialized) .swiper-slide,
            .swiper-newarrivals:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(50% - 7px) !important;
                width: calc(50% - 7px) !important;
                max-width: calc(50% - 7px) !important;
            }
        }

        @media (min-width: 768px) {

            .swiper-bestsellers:not(.swiper-initialized) .swiper-slide,
            .swiper-newarrivals:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(31.25% - 12.2px) !important;
                width: calc(31.25% - 12.2px) !important;
                max-width: calc(31.25% - 12.2px) !important;
            }
        }

        @media (min-width: 1025px) {

            .swiper-bestsellers:not(.swiper-initialized) .swiper-slide,
            .swiper-newarrivals:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(25% - 12px) !important;
                width: calc(25% - 12px) !important;
                max-width: calc(25% - 12px) !important;
            }
        }

        @media (min-width: 1441px) {

            .swiper-bestsellers:not(.swiper-initialized) .swiper-slide,
            .swiper-newarrivals:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(20% - 12.8px) !important;
                width: calc(20% - 12.8px) !important;
                max-width: calc(20% - 12.8px) !important;
            }
        }

        /* 3. Featured Products Swiper (Mobile 2, 481px 2, 768px 2.5, 1025px 3.5) */
        .swiper-featured:not(.swiper-initialized) .swiper-slide {
            flex: 0 0 calc(50% - 6px) !important;
            width: calc(50% - 6px) !important;
            max-width: calc(50% - 6px) !important;
        }

        @media (min-width: 481px) {
            .swiper-featured:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(50% - 7px) !important;
                width: calc(50% - 7px) !important;
                max-width: calc(50% - 7px) !important;
            }
        }

        @media (min-width: 768px) {
            .swiper-featured:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(40% - 12.8px) !important;
                width: calc(40% - 12.8px) !important;
                max-width: calc(40% - 12.8px) !important;
            }
        }

        @media (min-width: 1025px) {
            .swiper-featured:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(28.57% - 13.7px) !important;
                width: calc(28.57% - 13.7px) !important;
                max-width: calc(28.57% - 13.7px) !important;
            }
        }

        /* 4. Videos Swiper (Mobile 1, 481px 1, 768px 3.2, 1025px 4) */
        .swiper-videos:not(.swiper-initialized) .swiper-slide {
            flex: 0 0 100% !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        @media (min-width: 481px) {
            .swiper-videos:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 100% !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        @media (min-width: 768px) {
            .swiper-videos:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(31.25% - 15.2px) !important;
                width: calc(31.25% - 15.2px) !important;
                max-width: calc(31.25% - 15.2px) !important;
            }
        }

        @media (min-width: 1025px) {
            .swiper-videos:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(25% - 15px) !important;
                width: calc(25% - 15px) !important;
                max-width: calc(25% - 15px) !important;
            }
        }

        /* 5. Google Reviews Swiper (Mobile 1, 481px 1, 768px 2.2, 1025px 3) */
        .swiper-reviews:not(.swiper-initialized) .swiper-slide {
            flex: 0 0 100% !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        @media (min-width: 481px) {
            .swiper-reviews:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 100% !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        @media (min-width: 768px) {
            .swiper-reviews:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(45.45% - 14.5px) !important;
                width: calc(45.45% - 14.5px) !important;
                max-width: calc(45.45% - 14.5px) !important;
            }
        }

        @media (min-width: 1025px) {
            .swiper-reviews:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(33.33% - 12px) !important;
                width: calc(33.33% - 12px) !important;
                max-width: calc(33.33% - 12px) !important;
            }
        }

        /* 6. Compare Scooters Swiper (Mobile 1, 481px 1, 768px 2.2, 1025px 4) */
        .swiper-compare:not(.swiper-initialized) .swiper-slide {
            flex: 0 0 100% !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        @media (min-width: 481px) {
            .swiper-compare:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 100% !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        @media (min-width: 768px) {
            .swiper-compare:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(45.45% - 14.5px) !important;
                width: calc(45.45% - 14.5px) !important;
                max-width: calc(45.45% - 14.5px) !important;
            }
        }

        @media (min-width: 1025px) {
            .swiper-compare:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(25% - 15px) !important;
                width: calc(25% - 15px) !important;
                max-width: calc(25% - 15px) !important;
            }
        }

        /* 7. Brands Swiper (Mobile 2, 480px 3, 768px 4) */
        .swiper-brands:not(.swiper-initialized) .swiper-slide,
        .swiper-brands-premium:not(.swiper-initialized) .swiper-slide {
            flex: 0 0 calc(50% - 6px) !important;
            width: calc(50% - 6px) !important;
            max-width: calc(50% - 6px) !important;
        }

        @media (min-width: 480px) {

            .swiper-brands:not(.swiper-initialized) .swiper-slide,
            .swiper-brands-premium:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(33.33% - 9.3px) !important;
                width: calc(33.33% - 9.3px) !important;
                max-width: calc(33.33% - 9.3px) !important;
            }
        }

        @media (min-width: 768px) {

            .swiper-brands:not(.swiper-initialized) .swiper-slide,
            .swiper-brands-premium:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(25% - 12px) !important;
                width: calc(25% - 12px) !important;
                max-width: calc(25% - 12px) !important;
            }
        }

        /* 8. Articles Swiper (Mobile 1, 481px 1, 768px 3.1, 1025px 4) */
        .swiper-articles:not(.swiper-initialized) .swiper-slide {
            flex: 0 0 100% !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        @media (min-width: 481px) {
            .swiper-articles:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 100% !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        @media (min-width: 768px) {
            .swiper-articles:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(32.25% - 13.5px) !important;
                width: calc(32.25% - 13.5px) !important;
                max-width: calc(32.25% - 13.5px) !important;
            }
        }

        @media (min-width: 1025px) {
            .swiper-articles:not(.swiper-initialized) .swiper-slide {
                flex: 0 0 calc(25% - 18px) !important;
                width: calc(25% - 18px) !important;
                max-width: calc(25% - 18px) !important;
            }
        }

        /* ── Universal Glassmorphic Carousel Navigation Overlay Buttons ── */
        .carousel-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 25;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            color: #111827;
            display: flex !important;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .carousel-nav-btn svg,
        .carousel-nav-btn i {
            width: 16px !important;
            height: 16px !important;
        }

        .carousel-nav-btn:hover {
            background: rgba(255, 255, 255, 0.98);
            color: #A8111C;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
            transform: translateY(-50%) scale(1.08);
        }

        .carousel-nav-btn:active {
            transform: translateY(-50%) scale(0.92);
        }

        .carousel-nav-btn.swiper-button-disabled {
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .carousel-nav-prev {
            left: 4px;
        }

        .carousel-nav-next {
            right: 4px;
        }

        /* Desktop & Tablet Sizing (>= 768px) */
        @media (min-width: 768px) {
            .carousel-nav-btn {
                width: 40px;
                height: 40px;
                background: rgba(255, 255, 255, 0.75);
            }

            .carousel-nav-btn svg,
            .carousel-nav-btn i {
                width: 20px !important;
                height: 20px !important;
            }

            .carousel-nav-prev {
                left: -10px;
            }

            .carousel-nav-next {
                right: -10px;
            }
        }

        @media (min-width: 1280px) {
            .carousel-nav-prev {
                left: -14px;
            }

            .carousel-nav-next {
                right: -14px;
            }
        }
    </style>

    <!-- Swiper Slider v11 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Central Mudsor Toast & Logout Modal Systems -->
    <script src="<?= asset('js/toast.js') ?>"></script>
    <script src="<?= asset('js/logout-modal.js') ?>"></script>
</head>

<body class="bg-white text-gray-900 font-sans antialiased flex flex-col min-h-screen" x-data="{ 
        cartDrawer: false, topAnnouncement: true, authModal: false, authTab: 'login', 
        cartPromptToast: false, targetWishlistId: null, wholesaleModal: false, 
        mobileMenu: false, searchModal: false, searchQuery: '', searchResults: [], searchLoading: false,
        triggerGlobalSearch(query) {
            this.searchLoading = true;
            fetch('<?= url('api/search') ?>?q=' + encodeURIComponent(query || ''))
                .then(r => r.json())
                .then(data => {
                    this.searchLoading = false;
                    if (data && data.products) {
                        this.searchResults = data.products.map(i => ({
                            id: i.id,
                            name: i.name,
                            sku: i.sku || null,
                            price: '₹' + (parseFloat(i.sale_price) > 0 ? parseFloat(i.sale_price).toLocaleString('en-IN') : parseFloat(i.price).toLocaleString('en-IN')),
                            main_image: (i.main_image && i.main_image.startsWith('http')) ? i.main_image : '<?= rtrim(url(''), '/') ?>/' + (i.main_image || 'assets/images/placeholder.jpg').replace(/^\/+/, ''),
                            slug: i.slug || (i.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''))
                        }));
                    } else {
                        this.searchResults = [];
                    }
                })
                .catch(err => {
                    this.searchLoading = false;
                    this.searchResults = [];
                });
        }
    }" @keydown.escape.window="mobileMenu = false; searchModal = false"
    :class="(mobileMenu || searchModal) ? 'overflow-hidden' : ''">

    <!-- 1. Top Announcement Bar (Continuous Single-Line Marquee, Pauses on Hover) -->
    <?php if (!empty($announcement) && !empty($announcement['is_active'])): ?>
        <div x-show="topAnnouncement"
            class="w-full text-white text-xs py-2 px-4 flex items-center justify-between transition-all shadow-xs overflow-hidden relative"
            style="background-color: var(--color-primary-dark); box-sizing: border-box;">

            <div class="flex-1 overflow-hidden relative flex items-center announcement-marquee-container"
                style="height: 28px;">
                <div class="announcement-marquee-track font-medium flex items-center space-x-12">
                    <span class="inline-flex items-center shrink-0">
                        <span><?= htmlspecialchars_decode($announcement['message']) ?></span>
                    </span>
                    <span class="inline-flex items-center shrink-0">
                        <span><?= htmlspecialchars_decode($announcement['message']) ?></span>
                    </span>
                    <span class="inline-flex items-center shrink-0">
                        <span><?= htmlspecialchars_decode($announcement['message']) ?></span>
                    </span>
                    <span class="inline-flex items-center shrink-0">
                        <span><?= htmlspecialchars_decode($announcement['message']) ?></span>
                    </span>
                </div>
            </div>

            <div class="hidden sm:flex items-center space-x-2 shrink-0 pl-3 z-10"
                style="background-color: var(--color-primary-dark);">
                <?php if (!empty($announcement['cta_text'])): ?>
                    <a href="<?= url(ltrim($announcement['cta_link'] ?: 'shop', '/')) ?>"
                        class="inline-flex items-center justify-center space-x-1 bg-white text-theme-primary font-semibold text-[11px] px-3 py-1.5 min-h-[44px] rounded hover:bg-gray-100 transition shadow-xs">
                        <span><?= htmlspecialchars($announcement['cta_text']) ?></span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                <?php endif; ?>
                <button @click="topAnnouncement = false"
                    class="p-2 hover:bg-white/10 rounded transition text-white/80 hover:text-white min-h-[44px] min-w-[44px] flex items-center justify-center"
                    title="Close">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- 2. Utility Bar (Desktop / Tablet only) -->
    <div class="hidden md:block bg-gray-100 border-b border-gray-900 text-xs text-gray-600 py-1.5 px-4">
        <div class="container mx-auto flex justify-between items-center">
            <!-- Left: Phone Customer Support (Direct Call Link) -->
            <a href="tel:+919217714452"
                class="flex items-center space-x-1.5 font-semibold text-gray-900 hover:text-theme-primary transition">
                <i data-lucide="phone-call" class="w-3.5 h-3.5 text-theme-primary shrink-0"></i>
                <span>Call Us</span>
                <span class="hidden sm:inline text-gray-400 font-normal">|</span>
                <span class="hidden sm:inline text-gray-500 font-normal">Mon - Sat (9:00 AM - 6:00 PM IST)</span>
            </a>

            <!-- Right: Dynamic Social Links (admin-controlled via Settings) -->
            <?php
            // Inline SVG paths for brand icons (Lucide removed social brand icons)
            $socialSvgs = [
                'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
                'youtube' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
                'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
                'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.736-8.849L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
                'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
                'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
            ];
            $socialDefs = [
                ['key' => 'social_instagram', 'show_key' => 'show_social_instagram', 'svg_key' => 'instagram', 'label' => 'Instagram'],
                ['key' => 'social_youtube', 'show_key' => 'show_social_youtube', 'svg_key' => 'youtube', 'label' => 'YouTube'],
                ['key' => 'social_facebook', 'show_key' => 'show_social_facebook', 'svg_key' => 'facebook', 'label' => 'Facebook'],
                ['key' => 'social_twitter', 'show_key' => 'show_social_twitter', 'svg_key' => 'twitter', 'label' => 'Twitter / X'],
                ['key' => 'social_whatsapp', 'show_key' => 'show_social_whatsapp', 'svg_key' => 'whatsapp', 'label' => 'WhatsApp'],
                ['key' => 'social_linkedin', 'show_key' => 'show_social_linkedin', 'svg_key' => 'linkedin', 'label' => 'LinkedIn'],
            ];
            $activeSocials = [];
            foreach ($socialDefs as $sd) {
                $isOn = ($socialSettings[$sd['show_key']] ?? '0') === '1';
                $url = trim($socialSettings[$sd['key']] ?? '');
                if ($isOn && $url !== '') {
                    $activeSocials[] = ['svg' => $socialSvgs[$sd['svg_key']], 'url' => $url, 'label' => $sd['label']];
                }
            }
            ?>
            <?php if (!empty($activeSocials)): ?>
                <div class="flex items-center space-x-3">
                    <?php foreach ($activeSocials as $as): ?>
                        <a href="<?= htmlspecialchars($as['url']) ?>" target="_blank" rel="noopener noreferrer"
                            title="<?= htmlspecialchars($as['label']) ?>"
                            class="text-gray-500 hover:text-theme-primary transition-colors duration-150 flex items-center">
                            <?= $as['svg'] ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="site-global-header"
        class="relative left-0 w-full z-[60] bg-white flex flex-col transition-all duration-300">
        <!-- 3. Desktop Sticky Header Container (Contains Search Row + Nav Links Row Together) -->
        <header id="desktop-sticky-header"
            class="bg-white border-b border-gray-900 relative z-30 transition-all duration-200">
            
            <!-- Top Search & Logo Row -->
            <div class="py-1.5 md:py-2 px-3 md:px-4 border-b border-gray-100/70 transition-all duration-200">
                <div class="container mx-auto flex items-center justify-between gap-3 sm:gap-6">

                    <!-- Header Logo -->
                    <a href="<?= url('/') ?>" class="flex items-center shrink-0 group py-0.5 h-[38px] md:h-[48px]">
                        <img src="<?= asset('images/mudsor-logo.png') ?>" alt="Mudsor Logo"
                            class="h-full w-auto max-h-[38px] md:max-h-[48px] min-h-[32px] md:min-h-[42px] object-contain transition-transform group-hover:scale-105"
                            onerror="this.onerror=null; this.src='https://via.placeholder.com/180x48?text=MUDSOR';">
                    </a>

                    <!-- Large Centered Search Bar -->
                    <div class="flex-1 max-w-2xl hidden md:flex items-center relative"
                        @click.away="searchQuery = ''; searchResults = []">
                        <div
                            class="flex w-full border-2 border-theme-primary rounded-xl overflow-hidden bg-white items-center focus-within:border-red-700 focus-within:ring-2 focus-within:ring-red-100 transition">
                            <input type="text" x-model="searchQuery" x-ref="globalSearchInput"
                                @focus="triggerGlobalSearch(searchQuery)"
                                @input.debounce.250ms="triggerGlobalSearch(searchQuery)"
                                @keydown.enter.prevent="if (searchQuery.trim()) window.location.href = '<?= url('shop') ?>?search=' + encodeURIComponent(searchQuery)"
                                placeholder="Search electric scooter accessories, crash guards, covers..."
                                class="flex-1 h-10 px-4 text-xs font-medium text-gray-900 bg-transparent focus:outline-none placeholder-gray-500">

                            <template x-if="searchQuery">
                                <button type="button"
                                    @click="searchQuery = ''; searchResults = []; $refs.globalSearchInput.focus()"
                                    class="px-3 text-gray-400 hover:text-gray-600 transition">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>
                            </template>

                            <button type="button"
                                @click="if (searchQuery.trim()) window.location.href = '<?= url('shop') ?>?search=' + encodeURIComponent(searchQuery)"
                                class="h-10 px-5 bg-theme-primary hover:bg-theme-primary-dark text-white font-semibold text-xs flex items-center justify-center transition">
                                <i data-lucide="search" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <!-- Desktop Search Dropdown -->
                        <div x-show="searchQuery.trim().length > 0" x-cloak
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 translate-y-2"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden z-[100] max-h-[70vh] flex flex-col">
                            <div class="p-2 overflow-y-auto flex-1 space-y-1">
                                <template x-if="searchLoading">
                                    <div
                                        class="py-6 text-center text-xs font-semibold text-gray-400 flex items-center justify-center space-x-2">
                                        <div
                                            class="w-4 h-4 border-2 border-theme-primary border-t-transparent rounded-full animate-spin">
                                        </div>
                                        <span>Searching Mudsor store...</span>
                                    </div>
                                </template>

                                <template x-if="!searchLoading && searchResults.length > 0">
                                    <div class="divide-y divide-gray-50">
                                        <template x-for="item in searchResults" :key="item.id">
                                            <a :href="'<?= url('product/') ?>' + (item.slug || item.id)"
                                                class="p-2 flex items-center justify-between hover:bg-red-50/50 rounded-lg transition group">
                                                <div class="flex items-center space-x-3 min-w-0">
                                                    <img :src="item.main_image"
                                                        @error="$el.src='<?= asset('assets/images/mudsor-logo.png') ?>'"
                                                        class="w-10 h-10 rounded-md object-contain bg-white border border-gray-100 p-1 shrink-0">
                                                    <div class="min-w-0">
                                                        <p class="text-[11px] font-semibold text-gray-900 group-hover:text-theme-primary transition truncate"
                                                            x-text="item.name"></p>
                                                        <p class="text-[9px] text-gray-400 font-mono"
                                                            x-text="'SKU: ' + (item.sku || 'N/A')"></p>
                                                    </div>
                                                </div>
                                                <div class="text-right shrink-0 pl-2">
                                                    <span class="text-[11px] font-extrabold text-gray-900 block"
                                                        x-text="item.price"></span>
                                                </div>
                                            </a>
                                        </template>
                                    </div>
                                </template>

                                <template
                                    x-if="!searchLoading && searchQuery.trim().length > 0 && searchResults.length === 0">
                                    <div class="py-6 text-center space-y-1">
                                        <i data-lucide="package-search" class="w-6 h-6 text-gray-300 mx-auto"></i>
                                        <p class="text-[11px] font-semibold text-gray-600">No products found</p>
                                    </div>
                                </template>
                            </div>

                            <template x-if="searchResults.length > 0">
                                <div class="p-3 border-t border-gray-100 bg-gray-50 text-center">
                                    <a :href="'<?= url('shop') ?>?search=' + encodeURIComponent(searchQuery)"
                                        class="text-[11px] font-semibold text-theme-primary hover:text-red-700 flex items-center justify-center space-x-1">
                                        <span>View all results for "<span x-text="searchQuery"></span>"</span>
                                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Right-Side Icon Group: Theme | Compare | Orders | Wishlist | Account | Cart -->
                    <div class="flex items-center space-x-1 sm:space-x-5 text-gray-700 text-xs font-semibold shrink-0">
                        <!-- Theme Switcher Button -->
                        <button type="button" onclick="toggleStorefrontTheme()"
                            class="w-10 h-10 sm:w-auto sm:h-auto flex flex-row sm:flex-col items-center justify-center hover:text-theme-primary transition focus:outline-none cursor-pointer p-1"
                            title="Toggle Light / Dark Mode">
                            <i data-lucide="sun" id="sf-icon-sun" class="w-5 h-5 text-amber-500"></i>
                            <i data-lucide="moon" id="sf-icon-moon" class="w-5 h-5 text-indigo-400"
                                style="display: none;"></i>
                            <span class="hidden sm:inline text-[11px] mt-0.5" id="sf-theme-label">Light</span>
                        </button>

                        <!-- Compare Icon with Live Badge -->
                        <a href="<?= url('compare') ?>"
                            class="hidden md:flex flex-col items-center hover:text-theme-primary transition min-w-[44px] min-h-[44px] justify-center"
                            title="Compare Products">
                            <div class="relative">
                                <i data-lucide="git-compare" class="w-5 h-5 mb-0.5"></i>
                                <span id="compare-badge-count"
                                    class="<?= $compareCount > 0 ? '' : 'hidden' ?> compare-count-badge absolute -top-2 -right-2.5 bg-theme-primary text-white text-[10px] font-semibold min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center border-2 border-white leading-none shadow-xs"><?= $compareCount ?></span>
                            </div>
                            <span class="text-[11px]">Compare</span>
                        </a>

                        <a href="<?= url('account') ?>"
                            class="hidden lg:flex flex-col items-center hover:text-theme-primary transition">
                            <i data-lucide="package" class="w-5 h-5 mb-0.5"></i>
                            <span class="text-[11px]">Orders</span>
                        </a>

                        <!-- Wishlist Icon with Live Badge -->
                        <a href="<?= url('wishlist') ?>"
                            class="hidden md:flex flex-col items-center hover:text-theme-primary transition"
                            title="My Wishlist">
                            <div class="relative">
                                <i data-lucide="heart" class="w-5 h-5 mb-0.5"></i>
                                <span id="header-wishlist-count"
                                    class="<?= $wishlistCount > 0 ? '' : 'hidden' ?> wishlist-count-badge absolute -top-2 -right-2.5 bg-theme-primary text-white text-[10px] font-semibold min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center border-2 border-white leading-none shadow-xs"><?= $wishlistCount ?></span>
                            </div>
                            <span class="text-[11px]">Wishlist</span>
                        </a>

                        <!-- Inquire Icon Button (Mobile Only: Opens Wholesale Modal) -->
                        <button type="button" @click="wholesaleModal = true"
                            class="md:hidden w-10 h-10 flex items-center justify-center hover:text-theme-primary transition p-1 cursor-pointer focus:outline-none"
                            title="Wholesale & Bulk Inquiry">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </button>

                        <!-- Account Icon (Desktop Only: Amazon-Style Hover Popover) -->
                        <?php if (!empty($_SESSION['user_id'])): ?>
                            <div class="hidden md:flex relative items-center justify-center"
                                x-data="{ open: false, timeout: null }"
                                @mouseenter="clearTimeout(timeout); open = true"
                                @mouseleave="timeout = setTimeout(() => { open = false }, 250)">
                                <a href="<?= url('account') ?>"
                                    @click="open = !open"
                                    class="flex flex-col items-center justify-center hover:text-theme-primary transition focus:outline-none p-1 group cursor-pointer"
                                    title="My Account">
                                    <div class="relative">
                                        <i data-lucide="user-check" class="w-5 h-5 text-theme-primary group-hover:scale-110 transition-transform"></i>
                                    </div>
                                    <span class="text-[11px] font-semibold truncate max-w-[75px] mt-0.5 text-gray-800 group-hover:text-theme-primary transition-colors">
                                        <?= htmlspecialchars($_SESSION['user_name'] ?? 'Account') ?>
                                    </span>
                                </a>

                                <!-- Amazon-Style Flyout Popover Menu -->
                                <div x-show="open"
                                    x-cloak
                                    @click.away="open = false"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                    class="absolute right-0 top-full mt-2 w-64 sm:w-72 bg-white rounded-2xl shadow-2xl border border-gray-100 p-2.5 z-[100] text-xs font-medium">
                                    
                                    <!-- Pointer Caret Arrow (Top Right) -->
                                    <div class="absolute -top-1.5 right-4 w-3.5 h-3.5 bg-white border-t border-l border-gray-100 rotate-45 rounded-tl-xs z-10"></div>

                                    <!-- Profile Info Banner (Amazon Header Style) -->
                                    <div class="p-3 bg-gradient-to-r from-red-50/80 via-white to-gray-50 rounded-xl border border-red-100/60 mb-2 relative overflow-hidden">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-full bg-theme-primary text-white font-extrabold text-sm flex items-center justify-center shadow-xs shrink-0">
                                                <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-bold text-gray-900 truncate">
                                                    Hello, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                                                </p>
                                                <p class="text-[10px] text-gray-500 truncate">
                                                    <?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Menu Items -->
                                    <div class="space-y-1">
                                        <div class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">Your Account</div>
                                        
                                        <a href="<?= url('account') ?>"
                                            class="flex items-center space-x-3 px-3 py-2 text-gray-700 hover:text-theme-primary hover:bg-red-50/60 rounded-xl transition-all duration-150 group">
                                            <div class="w-7 h-7 rounded-lg bg-gray-100 group-hover:bg-red-100/70 text-gray-500 group-hover:text-theme-primary flex items-center justify-center transition-colors shrink-0">
                                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                            </div>
                                            <span class="font-semibold text-xs">My Account Dashboard</span>
                                        </a>

                                        <a href="<?= url('account') ?>"
                                            class="flex items-center space-x-3 px-3 py-2 text-gray-700 hover:text-theme-primary hover:bg-red-50/60 rounded-xl transition-all duration-150 group">
                                            <div class="w-7 h-7 rounded-lg bg-gray-100 group-hover:bg-red-100/70 text-gray-500 group-hover:text-theme-primary flex items-center justify-center transition-colors shrink-0">
                                                <i data-lucide="package" class="w-4 h-4"></i>
                                            </div>
                                            <span class="font-semibold text-xs">My Orders & Invoices</span>
                                        </a>

                                        <a href="<?= url('wishlist') ?>"
                                            class="flex items-center space-x-3 px-3 py-2 text-gray-700 hover:text-theme-primary hover:bg-red-50/60 rounded-xl transition-all duration-150 group">
                                            <div class="w-7 h-7 rounded-lg bg-gray-100 group-hover:bg-red-100/70 text-gray-500 group-hover:text-theme-primary flex items-center justify-center transition-colors shrink-0">
                                                <i data-lucide="heart" class="w-4 h-4"></i>
                                            </div>
                                            <span class="font-semibold text-xs">Saved Wishlist</span>
                                        </a>

                                        <a href="<?= url('compare') ?>"
                                            class="flex items-center space-x-3 px-3 py-2 text-gray-700 hover:text-theme-primary hover:bg-red-50/60 rounded-xl transition-all duration-150 group">
                                            <div class="w-7 h-7 rounded-lg bg-gray-100 group-hover:bg-red-100/70 text-gray-500 group-hover:text-theme-primary flex items-center justify-center transition-colors shrink-0">
                                                <i data-lucide="git-compare" class="w-4 h-4"></i>
                                            </div>
                                            <span class="font-semibold text-xs">Compare Products</span>
                                        </a>
                                    </div>

                                    <!-- Divider & Logout Button -->
                                    <div class="border-t border-gray-100 mt-2 pt-1.5">
                                        <a href="<?= url('logout') ?>"
                                            class="flex items-center space-x-3 px-3 py-2 text-red-600 hover:bg-red-50 rounded-xl transition-all duration-150 group font-bold">
                                            <div class="w-7 h-7 rounded-lg bg-red-50 group-hover:bg-red-100 text-red-600 flex items-center justify-center transition-colors shrink-0">
                                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                            </div>
                                            <span class="text-xs">Sign Out / Logout</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="<?= url('login') ?>" class="hidden md:flex flex-col items-center justify-center hover:text-theme-primary transition p-1"
                                title="Login or Sign Up">
                                <i data-lucide="user" class="w-5 h-5 mb-0.5"></i>
                                <span class="text-[11px]">Account</span>
                            </a>
                        <?php endif; ?>

                        <!-- Cart Icon -->
                        <button @click="cartDrawer = true" id="header-cart-btn"
                            class="hidden md:flex flex-col items-center hover:text-theme-primary transition focus:outline-none"
                            title="Shopping Cart">
                            <div class="relative">
                                <i data-lucide="shopping-bag" id="header-cart-icon" class="w-5 h-5 mb-0.5"></i>
                                <span id="header-cart-count"
                                    class="<?= $cartCount > 0 ? '' : 'hidden' ?> cart-count-badge absolute -top-2 -right-2.5 bg-theme-primary text-white text-[10px] font-semibold min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center border-2 border-white leading-none shadow-xs"><?= $cartCount ?></span>
                            </div>
                            <span class="text-[11px]">Cart</span>
                        </button>

                        <!-- Hamburger Menu Button (Mobile only) -->
                        <button type="button" @click="mobileMenu = true"
                            class="md:hidden w-10 h-10 flex items-center justify-center hover:text-theme-primary transition focus:outline-none p-1"
                            aria-label="Open navigation menu" aria-expanded="false">
                            <i data-lucide="menu" class="w-6 h-6"></i>
                        </button>
                    </div>

                </div>
            </div>

            <!-- Compact Mobile Search Bar Strip (Visible on screens <= 768px) -->
            <div id="mobile-sticky-search"
                class="px-3 py-2 bg-white border-b border-gray-900 md:hidden relative z-40 transition-all duration-300">
                <div class="relative" @click.away="searchQuery = ''; searchResults = []">
                    <div
                        class="flex w-full border border-gray-300 rounded-xl overflow-hidden bg-white items-center focus-within:border-theme-primary focus-within:ring-1 focus-within:ring-red-100 transition">
                        <input type="text" x-model="searchQuery" @focus="triggerGlobalSearch(searchQuery)"
                            @input.debounce.250ms="triggerGlobalSearch(searchQuery)"
                            @keydown.enter.prevent="if (searchQuery.trim()) window.location.href = '<?= url('shop') ?>?search=' + encodeURIComponent(searchQuery)"
                            placeholder="Search crash guards, covers, holders..."
                            class="flex-1 h-9 px-3 text-[11px] font-medium text-gray-900 bg-transparent focus:outline-none placeholder-gray-500">

                        <template x-if="searchQuery">
                            <button type="button" @click="searchQuery = ''; searchResults = []"
                                class="px-2 text-gray-400 hover:text-gray-600 transition">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        </template>

                        <button type="button"
                            @click="if (searchQuery.trim()) window.location.href = '<?= url('shop') ?>?search=' + encodeURIComponent(searchQuery)"
                            class="h-9 px-4 bg-theme-primary text-white flex items-center justify-center font-semibold text-xs transition"
                            aria-label="Search">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Mobile Search Dropdown -->
                    <div x-show="searchQuery.trim().length > 0" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute top-full left-0 right-0 mt-1 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden z-[100] max-h-[60vh] flex flex-col">
                        <div class="p-2 overflow-y-auto flex-1 space-y-1">
                            <template x-if="searchLoading">
                                <div
                                    class="py-4 text-center text-[10px] font-semibold text-gray-400 flex items-center justify-center space-x-2">
                                    <div
                                        class="w-3 h-3 border-2 border-theme-primary border-t-transparent rounded-full animate-spin">
                                    </div>
                                    <span>Searching...</span>
                                </div>
                            </template>

                            <template x-if="!searchLoading && searchResults.length > 0">
                                <div class="divide-y divide-gray-50">
                                    <template x-for="item in searchResults" :key="item.id">
                                        <a :href="'<?= url('product/') ?>' + (item.slug || item.id)"
                                            class="p-2 flex items-center justify-between hover:bg-red-50/50 rounded-lg transition group">
                                            <div class="flex items-center space-x-2 min-w-0">
                                                <img :src="item.main_image"
                                                    @error="$el.src='<?= asset('assets/images/mudsor-logo.png') ?>'"
                                                    class="w-8 h-8 rounded object-contain bg-white border border-gray-100 p-0.5 shrink-0">
                                                <div class="min-w-0">
                                                    <p class="text-[10px] font-semibold text-gray-900 truncate"
                                                        x-text="item.name"></p>
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0 pl-2">
                                                <span class="text-[10px] font-semibold text-gray-900 block"
                                                    x-text="item.price"></span>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <template x-if="!searchLoading && searchQuery.trim().length > 0 && searchResults.length === 0">
                                <div class="py-4 text-center">
                                    <p class="text-[10px] font-semibold text-gray-600">No products found</p>
                                </div>
                            </template>
                        </div>

                        <template x-if="searchResults.length > 0">
                            <div class="p-2 border-t border-gray-100 bg-gray-50 text-center">
                                <a :href="'<?= url('shop') ?>?search=' + encodeURIComponent(searchQuery)"
                                    class="text-[10px] font-semibold text-theme-primary flex items-center justify-center space-x-1">
                                    <span>View all results</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                </a>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- 4. Navigation Bar inside Sticky Header -->
            <nav class="main-desktop-nav hidden md:block bg-white text-xs font-semibold text-gray-800 z-20 relative">
                <div class="container mx-auto px-4 flex items-center justify-between" x-data="{ 
                    megaMenu: false, 
                    timer: null,
                    openMenu() {
                        if (this.timer) clearTimeout(this.timer);
                        this.megaMenu = true;
                    },
                    closeMenuWithDelay() {
                        if (this.timer) clearTimeout(this.timer);
                        this.timer = setTimeout(() => {
                            this.megaMenu = false;
                        }, 1000);
                    }
                }">

                    <div class="flex items-center space-x-1">
                        <!-- Mega Menu Trigger -->
                        <div class="relative py-1.5" @mouseenter="openMenu()" @mouseleave="closeMenuWithDelay()">
                            <button @click="megaMenu = !megaMenu"
                                class="bg-theme-primary text-white px-3.5 py-1.5 rounded-md font-semibold flex items-center space-x-2 hover:bg-theme-primary-dark transition shadow-xs">
                                <i data-lucide="menu" class="w-4 h-4"></i>
                                <span>All Categories</span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 ml-1"></i>
                            </button>

                            <!-- Mega Menu Dropdown -->
                            <div x-show="megaMenu" @mouseenter="openMenu()" @mouseleave="closeMenuWithDelay()"
                                @click.away="megaMenu = false"
                                class="absolute left-0 top-full w-[720px] bg-white border border-gray-900 rounded-b-xl shadow-2xl p-5 space-y-4 z-50"
                                x-cloak>

                                <div class="grid grid-cols-3 gap-4 max-h-[250px] overflow-y-auto pr-1">
                                    <?php foreach ($navCategories as $cat): ?>
                                        <?php
                                        $catImg = !empty($cat['custom_icon']) ? $cat['custom_icon'] : (!empty($cat['image']) ? $cat['image'] : (!empty($cat['icon']) && (str_contains($cat['icon'], '/') || str_contains($cat['icon'], '.')) ? $cat['icon'] : ''));
                                        ?>
                                        <div
                                            class="space-y-1.5 p-2 rounded-xl hover:bg-gray-50 transition border border-transparent hover:border-gray-100">
                                            <h4
                                                class="font-semibold text-theme-primary text-xs uppercase border-b border-gray-100 pb-1 flex items-center space-x-2">
                                                <?php if (!empty($catImg)): ?>
                                                    <img src="<?= asset($catImg) ?>" alt="<?= htmlspecialchars($cat['name']) ?>"
                                                        class="w-4 h-4 object-cover rounded-xs shrink-0">
                                                <?php else: ?>
                                                    <i data-lucide="image" class="w-3.5 h-3.5 text-theme-primary shrink-0"></i>
                                                <?php endif; ?>
                                                <span class="truncate"><?= htmlspecialchars($cat['name']) ?></span>
                                            </h4>
                                            <a href="<?= url('category/' . $cat['slug']) ?>"
                                                class="text-[11px] font-medium text-gray-600 hover:text-theme-primary block transition truncate">
                                                Browse All <?= htmlspecialchars($cat['name']) ?> →
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="pt-3 border-t border-gray-100 flex justify-between items-center text-xs">
                                    <span class="text-gray-400 text-[11px] font-medium">Scroll to explore all categories</span>
                                    <a href="<?= url('categories') ?>"
                                        class="font-semibold text-red-600 hover:underline flex items-center space-x-1">
                                        <span>View All Categories Page →</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Main Horizontal Links -->
                        <div class="hidden lg:flex items-center space-x-6 pl-4">
                            <a href="<?= url('/') ?>"
                                class="py-2 font-semibold transition <?= $isHome ? 'border-b-2 border-theme-primary text-theme-primary' : 'text-gray-700 hover:text-theme-primary' ?>">Home</a>

                            <a href="<?= url('shop') ?>"
                                class="py-2 font-semibold transition <?= $isShop ? 'border-b-2 border-theme-primary text-theme-primary' : 'text-gray-700 hover:text-theme-primary' ?>">Shop</a>

                            <a href="<?= url('brands') ?>"
                                class="py-2 font-semibold transition <?= $isBrand ? 'border-b-2 border-theme-primary text-theme-primary' : 'text-gray-700 hover:text-theme-primary' ?>">Brands</a>

                            <a href="<?= url('blog') ?>"
                                class="py-2 font-semibold transition <?= $isBlog ? 'border-b-2 border-theme-primary text-theme-primary' : 'text-gray-700 hover:text-theme-primary' ?>">Blog</a>

                            <a href="<?= url('about-us') ?>"
                                class="py-2 font-semibold transition <?= $isAbout ? 'border-b-2 border-theme-primary text-theme-primary font-semibold' : 'text-gray-700 hover:text-theme-primary' ?>">About Mudsor</a>

                            <a href="<?= url('contact-us') ?>"
                                class="py-2 font-semibold transition <?= $isContact ? 'border-b-2 border-theme-primary text-theme-primary font-semibold' : 'text-gray-700 hover:text-theme-primary' ?>">Contact Us</a>
                        </div>
                    </div>

                    <!-- Highlighted CTA Button -->
                    <div class="py-1.5">
                        <button type="button" @click="wholesaleModal = true"
                            class="px-4 py-1.5 bg-theme-primary hover:bg-theme-primary-dark text-white font-semibold text-xs rounded-lg transition shadow-xs flex items-center space-x-1.5 shrink-0 focus:outline-none cursor-pointer">
                            <i data-lucide="building-2" class="w-3.5 h-3.5 text-white/80"></i>
                            <span>Inquire for Wholesale</span>
                        </button>
                    </div>

                </div>
            </nav>
        </header>

    </div> <!-- End site-global-header -->

    <!-- Robust Sticky Header JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const desktopHeader = document.getElementById('desktop-sticky-header');
            const mobileSearch = document.getElementById('mobile-sticky-search');

            if (!desktopHeader || !mobileSearch) return;

            const desktopPlaceholder = document.createElement('div');
            desktopPlaceholder.style.display = 'none';
            desktopHeader.parentNode.insertBefore(desktopPlaceholder, desktopHeader);

            const mobilePlaceholder = document.createElement('div');
            mobilePlaceholder.style.display = 'none';
            mobileSearch.parentNode.insertBefore(mobilePlaceholder, mobileSearch);

            let activeHeader = null;
            let activePlaceholder = null;

            const onScroll = () => {
                const isMobile = window.innerWidth < 768;

                // Switching contexts
                if (isMobile) {
                    if (activeHeader === desktopHeader) {
                        desktopHeader.classList.remove('fixed', 'top-0', 'w-full', 'z-[60]', 'shadow-md');
                        desktopHeader.classList.add('relative');
                        desktopPlaceholder.style.display = 'none';
                    }
                    activeHeader = mobileSearch;
                    activePlaceholder = mobilePlaceholder;
                } else {
                    if (activeHeader === mobileSearch) {
                        mobileSearch.classList.remove('fixed', 'top-0', 'w-full', 'z-[60]', 'shadow-md');
                        mobileSearch.classList.add('relative');
                        mobilePlaceholder.style.display = 'none';
                    }
                    activeHeader = desktopHeader;
                    activePlaceholder = desktopPlaceholder;
                }

                const triggerPoint = activePlaceholder.style.display === 'block'
                    ? activePlaceholder.offsetTop
                    : activeHeader.offsetTop;

                if (window.scrollY > triggerPoint) {
                    if (activePlaceholder.style.display !== 'block') {
                        activePlaceholder.style.height = activeHeader.offsetHeight + 'px';
                        activePlaceholder.style.display = 'block';
                        activeHeader.classList.remove('relative');
                        activeHeader.classList.add('fixed', 'top-0', 'w-full', 'z-[60]', 'shadow-md');
                    }
                } else {
                    if (activePlaceholder.style.display === 'block') {
                        activePlaceholder.style.display = 'none';
                        activeHeader.classList.remove('fixed', 'top-0', 'w-full', 'z-[60]', 'shadow-md');
                        activeHeader.classList.add('relative');
                    }
                }
            };

            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', () => {
                onScroll(); // Re-eval which is active
                if (activePlaceholder && activePlaceholder.style.display === 'block') {
                    activePlaceholder.style.height = activeHeader.offsetHeight + 'px';
                }
            });
            // Initial check
            onScroll();
        });
    </script>


    <!-- ══════════════════════════════════════════════════════════════ -->
    <!-- MOBILE OFF-CANVAS SIDEBAR NAVIGATION                         -->
    <!-- Visible ONLY on mobile (<768px). Desktop/Tablet untouched.   -->
    <!-- ══════════════════════════════════════════════════════════════ -->

    <!-- Backdrop Overlay -->
    <div x-show="mobileMenu" @click="mobileMenu = false" x-transition:enter="transition-opacity duration-300 ease-out"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200 ease-in" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="md:hidden fixed inset-0 bg-black/55 backdrop-blur-sm z-[60]" x-cloak
        aria-hidden="true">
    </div>

    <!-- Sidebar Panel -->
    <div x-show="mobileMenu" x-transition:enter="transition-transform duration-300 ease-out"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform duration-200 ease-in" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="md:hidden fixed inset-y-0 left-0 w-[82vw] max-w-[320px] bg-white z-[70] flex flex-col shadow-2xl" x-cloak
        role="dialog" aria-modal="true" aria-label="Navigation menu" x-data="{ catOpen: false, policyOpen: false }">

        <!-- ── Sidebar Header ── -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 shrink-0 bg-white">
            <a href="<?= url('/') ?>" @click="mobileMenu = false" class="flex items-center h-10">
                <img src="<?= asset('images/mudsor-logo.png') ?>" alt="Mudsor" class="h-10 w-auto object-contain"
                    onerror="this.src='https://via.placeholder.com/140x40?text=MUDSOR';">
            </a>
            <button type="button" @click="mobileMenu = false"
                class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-theme-primary hover:bg-gray-100 rounded-xl transition"
                aria-label="Close menu">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- ── Auth Section ── -->
        <div class="px-4 py-3 border-b border-gray-100 shrink-0">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <!-- Logged In -->
                <div class="flex items-center space-x-3 mb-3">
                    <div
                        class="w-10 h-10 rounded-full bg-red-50 border border-red-100 flex items-center justify-center shrink-0">
                        <i data-lucide="user-check" class="w-5 h-5 text-theme-primary"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            <?= htmlspecialchars($_SESSION['user_name'] ?? 'My Account') ?>
                        </p>
                        <p class="text-[11px] text-gray-500 truncate"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="<?= url('account') ?>" @click="mobileMenu = false"
                        class="flex items-center justify-center space-x-1.5 h-9 px-3 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-semibold rounded-xl transition border border-gray-900">
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-theme-primary"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="<?= url('logout') ?>" @click="mobileMenu = false"
                        class="flex items-center justify-center space-x-1.5 h-9 px-3 bg-red-50 hover:bg-red-100 text-theme-primary text-xs font-semibold rounded-xl transition border border-red-100">
                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                        <span>Logout</span>
                    </a>
                </div>
            <?php else: ?>
                <!-- Logged Out -->
                <div class="grid grid-cols-2 gap-2">
                    <a href="<?= url('login') ?>" @click="mobileMenu = false"
                        class="flex items-center justify-center space-x-1.5 h-10 bg-theme-primary hover:bg-theme-primary-dark text-white text-xs font-semibold rounded-xl transition shadow-xs">
                        <i data-lucide="log-in" class="w-3.5 h-3.5"></i>
                        <span>Login</span>
                    </a>
                    <a href="<?= url('register') ?>" @click="mobileMenu = false"
                        class="flex items-center justify-center space-x-1.5 h-10 bg-gray-100 hover:bg-gray-900 text-gray-700 text-xs font-semibold rounded-xl transition border border-gray-900">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                        <span>Register</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Scrollable Nav Links ── -->
        <nav class="flex-1 overflow-y-auto overscroll-contain py-2 hide-scrollbar" role="navigation">

            <!-- Home -->
            <a href="<?= url('/') ?>" @click="mobileMenu = false"
                class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-gray-800 hover:text-theme-primary hover:bg-red-50/60 transition min-h-[44px]">
                <i data-lucide="home" class="w-4 h-4 text-theme-primary shrink-0"></i>
                <span>Home</span>
            </a>

            <!-- Shop -->
            <a href="<?= url('shop') ?>" @click="mobileMenu = false"
                class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-gray-800 hover:text-theme-primary hover:bg-red-50/60 transition min-h-[44px]">
                <i data-lucide="store" class="w-4 h-4 text-theme-primary shrink-0"></i>
                <span>Shop All Products</span>
            </a>

            <!-- Categories (expandable) -->
            <div x-data="{ open: false }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-gray-800 hover:text-theme-primary hover:bg-red-50/60 transition min-h-[44px]"
                    :aria-expanded="open">
                    <span class="flex items-center space-x-3">
                        <i data-lucide="layers" class="w-4 h-4 text-theme-primary shrink-0"></i>
                        <span>Categories</span>
                    </span>
                    <i data-lucide="chevron-down"
                        class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-transition:enter="transition-all duration-200 ease-out"
                    x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-[500px]"
                    x-transition:leave="transition-all duration-150 ease-in"
                    x-transition:leave-start="opacity-100 max-h-[500px]" x-transition:leave-end="opacity-0 max-h-0"
                    class="bg-gray-50/70 border-y border-gray-100 overflow-hidden" x-cloak>
                    <!-- All Categories link -->
                    <a href="<?= url('categories') ?>" @click="mobileMenu = false"
                        class="flex items-center space-x-2 px-8 py-2.5 text-xs font-semibold text-theme-primary hover:bg-red-50 transition min-h-[40px]">
                        <i data-lucide="grid" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>View All Categories</span>
                    </a>
                    <?php foreach ($navCategories as $cat): ?>
                        <a href="<?= url('category/' . $cat['slug']) ?>" @click="mobileMenu = false"
                            class="flex items-center space-x-2 px-8 py-2.5 text-xs font-semibold text-gray-700 hover:text-theme-primary hover:bg-red-50/60 transition border-t border-gray-100 min-h-[40px]">
                            <i data-lucide="<?= htmlspecialchars($cat['icon'] ?: 'tag') ?>"
                                class="w-3.5 h-3.5 text-theme-primary shrink-0"></i>
                            <span><?= htmlspecialchars($cat['name']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Brands -->
            <div x-data="{ open: false }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-gray-800 hover:text-theme-primary hover:bg-red-50/60 transition min-h-[44px]"
                    :aria-expanded="open">
                    <span class="flex items-center space-x-3">
                        <i data-lucide="shield" class="w-4 h-4 text-theme-primary shrink-0"></i>
                        <span>Brands</span>
                    </span>
                    <i data-lucide="chevron-down"
                        class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-transition:enter="transition-all duration-200 ease-out"
                    x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-[400px]"
                    x-transition:leave="transition-all duration-150 ease-in"
                    x-transition:leave-start="opacity-100 max-h-[400px]" x-transition:leave-end="opacity-0 max-h-0"
                    class="bg-gray-50/70 border-y border-gray-100 overflow-hidden" x-cloak>
                    <a href="<?= url('brands') ?>" @click="mobileMenu = false"
                        class="flex items-center space-x-2 px-8 py-2.5 text-xs font-semibold text-theme-primary hover:bg-red-50 transition min-h-[40px]">
                        <i data-lucide="grid" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>View All Brands</span>
                    </a>
                    <?php foreach ($navBrands as $brand): ?>
                        <a href="<?= url('brand/' . urlencode($brand['slug'] ?? $brand['id'])) ?>"
                            @click="mobileMenu = false"
                            class="flex items-center space-x-2 px-8 py-2.5 text-xs font-semibold text-gray-700 hover:text-theme-primary hover:bg-red-50/60 transition border-t border-gray-100 min-h-[40px]">
                            <i data-lucide="chevron-right" class="w-3 h-3 text-gray-400 shrink-0"></i>
                            <span><?= htmlspecialchars($brand['name']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Divider -->
            <div class="mx-4 my-1 border-t border-gray-100"></div>

            <!-- Wishlist with live count -->
            <a href="<?= url('wishlist') ?>" @click="mobileMenu = false"
                class="flex items-center justify-between px-4 py-3 text-sm font-semibold text-gray-800 hover:text-theme-primary hover:bg-red-50/60 transition min-h-[44px]">
                <span class="flex items-center space-x-3">
                    <i data-lucide="heart" class="w-4 h-4 text-theme-primary shrink-0"></i>
                    <span>Wishlist</span>
                </span>
                <?php if ($wishlistCount > 0): ?>
                    <span
                        class="bg-theme-primary text-white text-[10px] font-semibold min-w-[20px] h-5 px-1.5 rounded-full flex items-center justify-center leading-none"><?= $wishlistCount ?></span>
                <?php endif; ?>
            </a>

            <!-- Compare with live count -->
            <a href="<?= url('compare') ?>" @click="mobileMenu = false"
                class="flex items-center justify-between px-4 py-3 text-sm font-semibold text-gray-800 hover:text-theme-primary hover:bg-red-50/60 transition min-h-[44px]">
                <span class="flex items-center space-x-3">
                    <i data-lucide="git-compare" class="w-4 h-4 text-theme-primary shrink-0"></i>
                    <span>Compare</span>
                </span>
                <?php if ($compareCount > 0): ?>
                    <span
                        class="bg-theme-primary text-white text-[10px] font-semibold min-w-[20px] h-5 px-1.5 rounded-full flex items-center justify-center leading-none"><?= $compareCount ?></span>
                <?php endif; ?>
            </a>

            <!-- Orders (always visible) -->
            <a href="<?= url('account') ?>" @click="mobileMenu = false"
                class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-gray-800 hover:text-theme-primary hover:bg-red-50/60 transition min-h-[44px]">
                <i data-lucide="package" class="w-4 h-4 text-theme-primary shrink-0"></i>
                <span>My Orders</span>
            </a>


            <!-- Blog -->
            <a href="<?= url('blog') ?>" @click="mobileMenu = false"
                class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-gray-800 hover:text-theme-primary hover:bg-red-50/60 transition min-h-[44px]">
                <i data-lucide="newspaper" class="w-4 h-4 text-theme-primary shrink-0"></i>
                <span>Blog &amp; Articles</span>
            </a>

            <!-- Divider -->
            <div class="mx-4 my-1 border-t border-gray-100"></div>

            <!-- About Us -->
            <a href="<?= url('about-us') ?>" @click="mobileMenu = false"
                class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-gray-800 hover:text-theme-primary hover:bg-red-50/60 transition min-h-[44px]">
                <i data-lucide="info" class="w-4 h-4 text-theme-primary shrink-0"></i>
                <span>About Us</span>
            </a>

            <!-- Contact Us -->
            <a href="<?= url('contact-us') ?>" @click="mobileMenu = false"
                class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-gray-800 hover:text-theme-primary hover:bg-red-50/60 transition min-h-[44px]">
                <i data-lucide="phone" class="w-4 h-4 text-theme-primary shrink-0"></i>
                <span>Contact Us</span>
            </a>

            <!-- Wholesale Inquiry -->
            <button type="button" @click="mobileMenu = false; $nextTick(() => { wholesaleModal = true })"
                class="w-full flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-gray-800 hover:text-theme-primary hover:bg-red-50/60 transition min-h-[44px]">
                <i data-lucide="building-2" class="w-4 h-4 text-theme-primary shrink-0"></i>
                <span>Inquiry for Wholesale</span>
            </button>


            <!-- Divider -->
            <div class="mx-4 my-1 border-t border-gray-100"></div>

            <!-- Policies (expandable) -->
            <div x-data="{ open: false }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-gray-800 hover:text-theme-primary hover:bg-red-50/60 transition min-h-[44px]"
                    :aria-expanded="open">
                    <span class="flex items-center space-x-3">
                        <i data-lucide="file-text" class="w-4 h-4 text-theme-primary shrink-0"></i>
                        <span>Policies & Legal</span>
                    </span>
                    <i data-lucide="chevron-down"
                        class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-transition:enter="transition-all duration-200 ease-out"
                    x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-[300px]"
                    x-transition:leave="transition-all duration-150 ease-in"
                    x-transition:leave-start="opacity-100 max-h-[300px]" x-transition:leave-end="opacity-0 max-h-0"
                    class="bg-gray-50/70 border-y border-gray-100 overflow-hidden" x-cloak>
                    <a href="<?= url('privacy-policy') ?>" @click="mobileMenu = false"
                        class="flex items-center space-x-2 px-8 py-2.5 text-xs font-semibold text-gray-700 hover:text-theme-primary hover:bg-red-50/60 transition min-h-[40px]">
                        <i data-lucide="shield" class="w-3.5 h-3.5 text-theme-primary shrink-0"></i><span>Privacy
                            Policy</span>
                    </a>
                    <a href="<?= url('terms-conditions') ?>" @click="mobileMenu = false"
                        class="flex items-center space-x-2 px-8 py-2.5 text-xs font-semibold text-gray-700 hover:text-theme-primary hover:bg-red-50/60 transition border-t border-gray-100 min-h-[40px]">
                        <i data-lucide="scroll" class="w-3.5 h-3.5 text-theme-primary shrink-0"></i><span>Terms &
                            Conditions</span>
                    </a>
                    <a href="<?= url('refund-policy') ?>" @click="mobileMenu = false"
                        class="flex items-center space-x-2 px-8 py-2.5 text-xs font-semibold text-gray-700 hover:text-theme-primary hover:bg-red-50/60 transition border-t border-gray-100 min-h-[40px]">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-theme-primary shrink-0"></i><span>Refund
                            Policy</span>
                    </a>
                    <a href="<?= url('shipping-policy') ?>" @click="mobileMenu = false"
                        class="flex items-center space-x-2 px-8 py-2.5 text-xs font-semibold text-gray-700 hover:text-theme-primary hover:bg-red-50/60 transition border-t border-gray-100 min-h-[40px]">
                        <i data-lucide="truck" class="w-3.5 h-3.5 text-theme-primary shrink-0"></i><span>Shipping
                            Policy</span>
                    </a>
                </div>
            </div>

            <!-- Support -->
            <a href="tel:+919217714452" @click="mobileMenu = false"
                class="flex items-center space-x-3 px-4 py-3 text-sm font-semibold text-gray-800 hover:text-theme-primary hover:bg-red-50/60 transition min-h-[44px]">
                <i data-lucide="headphones" class="w-4 h-4 text-theme-primary shrink-0"></i>
                <span>Call Support</span>
            </a>

            <!-- Bottom padding for safe area -->
            <div class="h-6"></div>
        </nav>

        <!-- ── Sidebar Footer: Social Links ── -->
        <?php if (!empty($activeSocials)): ?>
            <div class="px-4 py-3 border-t border-gray-100 flex items-center space-x-4 shrink-0 bg-gray-50">
                <?php foreach ($activeSocials as $as): ?>
                    <a href="<?= htmlspecialchars($as['url']) ?>" target="_blank" rel="noopener noreferrer"
                        title="<?= htmlspecialchars($as['label']) ?>"
                        class="text-gray-400 hover:text-theme-primary transition-colors w-8 h-8 flex items-center justify-center">
                        <?= $as['svg'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
    <!-- END MOBILE SIDEBAR -->

    <!-- WHOLESALE INQUIRY MODAL FORM -->
    <div x-show="wholesaleModal"
        class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/65 backdrop-blur-xs transition-opacity"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>

        <div @click.away="wholesaleModal = false"
            class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative text-gray-900 font-sans border border-gray-100">

            <!-- Close Button -->
            <button type="button" @click="wholesaleModal = false"
                class="absolute top-4 right-4 p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-full transition cursor-pointer"
                title="Close">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <div class="flex items-center space-x-3 mb-3">
                <div
                    class="w-10 h-10 rounded-xl bg-red-50 text-theme-primary flex items-center justify-center shrink-0">
                    <i data-lucide="building-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-gray-900 leading-tight">Inquire for Wholesale</h3>
                    <p class="text-xs text-gray-500 font-medium">Get bulk pricing & dealer quotes directly from Mudsor.
                    </p>
                </div>
            </div>

            <div id="wholesale-modal-alert" class="hidden mb-3 p-3 rounded-xl text-xs font-semibold"></div>

            <form id="wholesale-inquiry-form" onsubmit="handleWholesaleSubmit(event)" class="space-y-3 pt-1">
                <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">

                <div>
                    <label class="block text-[11px] font-semibold text-gray-700 mb-1">Full Name <span
                            class="text-red-600">*</span></label>
                    <input type="text" name="name" required placeholder="Enter your full name"
                        class="w-full h-10 px-3 bg-gray-50 border border-gray-300 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-theme-primary focus:bg-white transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Phone / WhatsApp <span
                                class="text-red-600">*</span></label>
                        <input type="tel" name="phone" required placeholder="+91 9876543210"
                            class="w-full h-10 px-3 bg-gray-50 border border-gray-300 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-theme-primary focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Email Address <span
                                class="text-red-600">*</span></label>
                        <input type="email" name="email" required placeholder="name@example.com"
                            class="w-full h-10 px-3 bg-gray-50 border border-gray-300 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-theme-primary focus:bg-white transition">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Shop / Business Name</label>
                        <input type="text" name="company" placeholder="e.g. EV Accessories Hub"
                            class="w-full h-10 px-3 bg-gray-50 border border-gray-300 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-theme-primary focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Estimated Quantity</label>
                        <input type="text" name="quantity" placeholder="e.g. 50 pcs"
                            class="w-full h-10 px-3 bg-gray-50 border border-gray-300 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-theme-primary focus:bg-white transition">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-gray-700 mb-1">Message / Requirements</label>
                    <textarea name="message" rows="3"
                        placeholder="Specify products (crash guards, covers, holders) you need in bulk..."
                        class="w-full p-3 bg-gray-50 border border-gray-300 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-theme-primary focus:bg-white transition"></textarea>
                </div>

                <button type="submit" id="wholesale-submit-btn"
                    class="w-full h-11 bg-theme-primary hover:bg-theme-primary-dark text-white font-semibold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2 cursor-pointer">
                    <span>Submit Wholesale Inquiry</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>

        </div>
    </div>

    <script>
        function handleWholesaleSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const btn = document.getElementById('wholesale-submit-btn');
            const alertBox = document.getElementById('wholesale-modal-alert');

            btn.disabled = true;
            btn.innerHTML = 'Submitting Inquiry...';

            const formData = new FormData(form);

            fetch('<?= url('wholesale/inquire') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = '<span>Submit Wholesale Inquiry</span> <i data-lucide="arrow-right" class="w-4 h-4"></i>';
                    if (typeof lucide !== 'undefined') lucide.createIcons();

                    alertBox.classList.remove('hidden', 'bg-red-50', 'text-red-700', 'bg-green-50', 'text-green-700');
                    if (data.status === 'success') {
                        alertBox.classList.add('bg-green-50', 'text-green-700', 'border', 'border-green-200');
                        alertBox.innerHTML = '✓ ' + data.message;
                        form.reset();
                        if (window.MudsorToast) {
                            window.MudsorToast.show('Wholesale inquiry submitted successfully!', 'success', 3000);
                        }
                        setTimeout(() => {
                            alertBox.classList.add('hidden');
                            const bodyElem = document.querySelector('body');
                            if (bodyElem && bodyElem._x_dataStack) {
                                bodyElem._x_dataStack[0].wholesaleModal = false;
                            }
                        }, 2500);
                    } else {
                        alertBox.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-200');
                        alertBox.innerHTML = '⚠ ' + (data.message || 'Error submitting inquiry.');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = '<span>Submit Wholesale Inquiry</span>';
                    alertBox.classList.remove('hidden', 'bg-green-50', 'text-green-700');
                    alertBox.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-200');
                    alertBox.innerHTML = '⚠ Failed to submit inquiry. Please try again.';
                });
        }
    </script>