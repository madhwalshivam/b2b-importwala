<?php
use App\Core\Auth;
$currentUser = Auth::user();
$currentUri = trim($_GET['url'] ?? '', '/');

// Helper to highlight active navigation button
$isActive = function(string $path) use ($currentUri): bool {
    $cleanPath = trim($path, '/');
    if ($cleanPath === 'admin/dashboard') {
        return $currentUri === 'admin/dashboard' || $currentUri === 'admin';
    }
    return str_starts_with($currentUri, $cleanPath);
};
?>
<aside class="w-72 bg-slate-900 text-slate-200 min-h-screen flex flex-col border-r border-slate-800 shrink-0 font-sans shadow-md">

    <!-- Sidebar Header - Official ImportWala Logo Only -->
    <div class="h-[76px] px-5 border-b border-slate-800 flex items-center justify-center bg-slate-950 shrink-0">
        <a href="<?= url('admin/dashboard') ?>" class="flex items-center justify-center">
            <div class="px-3 py-1.5 bg-white rounded-xl shadow-sm inline-block">
                <img src="<?= url('assets/images/importwale-logo.png') ?>" alt="IMPORTWALA" class="h-8 w-auto object-contain">
            </div>
        </a>
    </div>

    <!-- 2-Column Grid Button Navigation -->
    <nav class="flex-1 p-3 space-y-4 text-xs font-medium overflow-y-auto">

        <!-- MAIN & ANALYTICS -->
        <?php if (Auth::canAccessModule('dashboard')): ?>
            <div>
                <div class="px-1 mb-1.5 text-[10px] font-semibold uppercase text-slate-400 tracking-wider">Main</div>
                <div class="grid grid-cols-2 gap-2">
                    <a href="<?= url('admin/dashboard') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/dashboard') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Dashboard</span>
                    </a>

                    <a href="<?= url('admin/analytics') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/analytics') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-red-400 border-slate-700/60' ?>">
                        <i data-lucide="activity" class="w-4 h-4 shrink-0 text-red-400"></i>
                        <span class="truncate">Analytics</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- CONTENT & MARKETING -->
        <div>
            <div class="px-1 mb-1.5 text-[10px] font-semibold uppercase text-slate-400 tracking-wider">Content</div>
            <div class="grid grid-cols-2 gap-2">
                <a href="<?= url('admin/navigation') ?>"
                    class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/navigation') ? 'bg-[#f05a29] text-white border-[#f05a29] shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-orange-400 border-slate-700/60' ?>"
                    title="Top Navigation Menu Manager">
                    <i data-lucide="compass" class="w-4 h-4 shrink-0 text-orange-400"></i>
                    <span class="truncate">Navigation</span>
                </a>

                <a href="<?= url('admin/announcement') ?>"
                    class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/announcement') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                    title="Announcement Bar">
                    <i data-lucide="megaphone" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">Announce</span>
                </a>

                <a href="<?= url('admin/banners') ?>"
                    class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/banners') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                    title="Hero Banners">
                    <i data-lucide="image" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">Banners</span>
                </a>

                <a href="<?= url('admin/featured-categories') ?>"
                    class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/featured-categories') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                    title="Featured Categories">
                    <i data-lucide="layers" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">Feat. Cats</span>
                </a>

                <a href="<?= url('admin/homepage-sections') ?>"
                    class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/homepage-sections') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                    title="Homepage Sections">
                    <i data-lucide="layout" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">Home Sec.</span>
                </a>

                <a href="<?= url('admin/collection-cards') ?>"
                    class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/collection-cards') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                    title="Product Collection Cards">
                    <i data-lucide="layout-grid" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">Collections</span>
                </a>

                <a href="<?= url('admin/videos') ?>"
                    class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/videos') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                    title="Homepage Videos">
                    <i data-lucide="video" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">Videos</span>
                </a>

                <?php if (Auth::canAccessModule('blogs')): ?>
                    <a href="<?= url('admin/blogs') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/blogs') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                        title="Blogs & Articles">
                        <i data-lucide="file-text" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Blogs</span>
                    </a>
                <?php endif; ?>

                <a href="<?= url('admin/google-reviews') ?>"
                    class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/google-reviews') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                    title="Google Reviews">
                    <i data-lucide="star" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">Reviews</span>
                </a>
            </div>
        </div>

        <!-- STORE & CATALOG -->
        <div>
            <div class="px-1 mb-1.5 text-[10px] font-semibold uppercase text-slate-400 tracking-wider">Catalog & Sales</div>
            <div class="grid grid-cols-2 gap-2">
                <a href="<?= url('admin/inquiries') ?>"
                    class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/inquiries') ? 'bg-[#f05a29] text-white border-[#f05a29] shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-orange-400 border-slate-700/60' ?>"
                    title="B2B Customer Inquiries">
                    <i data-lucide="clipboard-list" class="w-4 h-4 shrink-0 text-orange-400"></i>
                    <span class="truncate">Inquiries</span>
                </a>

                <a href="<?= url('admin/rfq') ?>"
                    class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/rfq') ? 'bg-[#f05a29] text-white border-[#f05a29] shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-orange-400 border-slate-700/60' ?>"
                    title="RFQ Requests">
                    <i data-lucide="file-search" class="w-4 h-4 shrink-0 text-orange-400"></i>
                    <span class="truncate">RFQ</span>
                    <?php
                      try {
                        $rfqBadge = (new \App\Models\RfqRequest())->getNewCount();
                        if ($rfqBadge > 0):
                    ?>
                    <span class="ml-auto flex-shrink-0 bg-[#f05a29] text-white text-[9px] font-black px-1.5 py-0.5 rounded-full leading-none"><?= $rfqBadge ?></span>
                    <?php endif; } catch (\Throwable $e) {} ?>
                </a>

                <a href="<?= url('admin/coupons') ?>"
                    class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/coupons') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                    title="Discount Coupons">
                    <i data-lucide="ticket" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">Coupons</span>
                </a>

                <?php if (Auth::canAccessModule('products')): ?>
                    <a href="<?= url('admin/products') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/products') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                        title="Products & Accessories">
                        <i data-lucide="package" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Products</span>
                    </a>

                    <a href="<?= url('admin/reviews') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/reviews') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                        title="Product Ratings">
                        <i data-lucide="message-square" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Ratings</span>
                    </a>

                    <a href="<?= url('admin/brands') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/brands') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                        title="Scooter Brands">
                        <i data-lucide="zap" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Brands</span>
                    </a>

                    <a href="<?= url('admin/categories') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/categories') ? 'bg-[#f05a29] text-white border-[#f05a29] shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                        title="Categories">
                        <i data-lucide="layers" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Categories</span>
                    </a>

                    <a href="<?= url('admin/subcategories') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/subcategories') ? 'bg-[#f05a29] text-white border-[#f05a29] shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                        title="Sub-categories">
                        <i data-lucide="folder-tree" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Sub-cats</span>
                    </a>
                <?php endif; ?>

                <?php if (Auth::canAccessModule('orders') || Auth::hasPermission('orders.view')): ?>
                    <a href="<?= url('admin/orders') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/orders') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                        title="Customer Orders">
                        <i data-lucide="shopping-bag" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Orders</span>
                    </a>

                    <a href="<?= url('admin/reports/sales-tax') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/reports/sales-tax') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                        title="Monthly Sales & Tax Report">
                        <i data-lucide="file-text" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Tax Report</span>
                    </a>
                <?php endif; ?>

                <?php if (Auth::canAccessModule('inventory')): ?>
                    <a href="<?= url('admin/inventory') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/inventory') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                        title="Inventory Stock">
                        <i data-lucide="boxes" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Inventory</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- ACCESS & SETTINGS -->
        <div>
            <div class="px-1 mb-1.5 text-[10px] font-semibold uppercase text-slate-400 tracking-wider">Control & Config</div>
            <div class="grid grid-cols-2 gap-2">
                <?php if (Auth::canAccessModule('employees')): ?>
                    <a href="<?= url('admin/employees') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/employees') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                        title="Employees Staff">
                        <i data-lucide="users" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Employees</span>
                    </a>

                    <a href="<?= url('admin/roles') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/roles') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                        title="Roles Permissions">
                        <i data-lucide="shield" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Roles</span>
                    </a>
                <?php endif; ?>

                <?php if (Auth::canAccessModule('logs')): ?>
                    <a href="<?= url('admin/logs') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/logs') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                        title="Activity Logs">
                        <i data-lucide="file-clock" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Logs</span>
                    </a>
                <?php endif; ?>

                <a href="<?= url('admin/notification-settings') ?>"
                    class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/notification-settings') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                    title="Notification Alerts">
                    <i data-lucide="bell" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">Alerts</span>
                </a>

                <?php if (Auth::canAccessModule('settings')): ?>
                    <a href="<?= url('admin/settings/payment-shipping') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/settings/payment-shipping') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-red-400 border-slate-700/60' ?>"
                        title="Razorpay & Shiprocket Integration">
                        <i data-lucide="credit-card" class="w-4 h-4 shrink-0 text-red-400"></i>
                        <span class="truncate">Gateway</span>
                    </a>

                    <a href="<?= url('admin/settings') ?>"
                        class="flex items-center space-x-2 px-3 py-2.5 rounded-xl border text-[11px] font-semibold transition cursor-pointer <?= $isActive('admin/settings') && !$isActive('admin/settings/payment-shipping') ? 'bg-red-600 text-white border-red-500 shadow-xs' : 'bg-slate-800/60 hover:bg-slate-800 text-slate-200 border-slate-700/60' ?>"
                        title="Site Settings">
                        <i data-lucide="settings" class="w-4 h-4 shrink-0"></i>
                        <span class="truncate">Settings</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

    </nav>

</aside>