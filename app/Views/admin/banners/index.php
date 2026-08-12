<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="space-y-6">

    <!-- Top Header -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span
                    class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Storefront Control
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Hero Slider Banners</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Hero Banner Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Upload or link high-resolution images for the homepage main hero banner slider. Recommended size: 1400 ×
                480 px.
            </p>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_GET['success'])): ?>
        <div
            class="bg-green-50 border border-green-200 text-green-800 text-xs font-semibold px-4 py-3 rounded-xl flex items-center space-x-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-green-600 shrink-0"></i>
            <span><?= htmlspecialchars($_GET['success']) ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div
            class="bg-red-50 border border-red-200 text-red-800 text-xs font-semibold px-4 py-3 rounded-xl flex items-center space-x-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
            <span><?= htmlspecialchars($_GET['error']) ?></span>
        </div>
    <?php endif; ?>

    <!-- Add New Banner Card -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center space-x-2">
            <i data-lucide="image-plus" class="w-4 h-4 text-red-600 dark:text-red-500"></i>
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Add New Banner</h2>
        </div>
        <form action="<?= url('admin/banners/store') ?>" method="POST" enctype="multipart/form-data"
            class="p-5 space-y-4">
            <?= csrf_field() ?>

            <!-- Title -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Banner Title <span
                        class="text-gray-400 dark:text-gray-500 font-normal">(internal label, not shown on frontend)</span></label>
                <input type="text" name="title" placeholder="e.g. Summer Sale Banner"
                    class="w-full h-10 px-3 border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-xl text-xs text-gray-900 dark:text-gray-100 focus:outline-none focus:border-red-600 focus:ring-1 focus:ring-red-500">
            </div>

            <!-- Device-Specific Banner Uploads -->
            <div class="bg-gray-50/70 dark:bg-slate-900/80 p-4 rounded-2xl border border-gray-200 dark:border-slate-700 space-y-3">
                <h3 class="text-xs font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider flex items-center space-x-1.5">
                    <i data-lucide="monitor-smartphone" class="w-4 h-4 text-red-600 dark:text-red-500"></i>
                    <span>Device-Specific Banner Uploads</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- ── Desktop Banner ── -->
                    <div x-data="bannerUploader('desktop')"
                        class="bg-white dark:bg-slate-800 p-3 rounded-xl border border-gray-200 dark:border-slate-700 shadow-xs space-y-2">
                        <div class="flex items-center space-x-1.5 mb-1">
                            <i data-lucide="monitor" class="w-4 h-4 text-red-600 dark:text-red-500"></i>
                            <label class="text-xs font-semibold text-gray-800 dark:text-gray-200">Desktop Banner <span
                                    class="text-gray-400 dark:text-gray-500 font-normal">(1025px+)</span></label>
                        </div>
                        <!-- Toggle Buttons -->
                        <div class="flex space-x-1.5">
                            <button type="button" @click="method='upload'"
                                :class="method==='upload' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300'"
                                class="h-6 px-2.5 rounded-md text-[10px] font-semibold transition flex items-center space-x-1">
                                <i data-lucide="upload" class="w-3 h-3"></i><span>Upload</span>
                            </button>
                            <button type="button" @click="method='url'"
                                :class="method==='url' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300'"
                                class="h-6 px-2.5 rounded-md text-[10px] font-semibold transition flex items-center space-x-1">
                                <i data-lucide="link" class="w-3 h-3"></i><span>URL</span>
                            </button>
                        </div>
                        <!-- Upload -->
                        <div x-show="method==='upload'" class="space-y-1">
                            <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp,image/gif"
                                @change="previewFile($event)"
                                class="block w-full text-xs text-gray-600 dark:text-gray-300 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-red-50 dark:file:bg-red-950/60 file:text-red-700 dark:file:text-red-300 hover:file:bg-red-100 cursor-pointer">
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 flex items-center space-x-1">
                                <i data-lucide="ruler" class="w-3 h-3"></i><span>Rec: 1400 × 480 px</span>
                            </p>
                        </div>
                        <!-- URL -->
                        <div x-show="method==='url'" x-cloak>
                            <input type="url" name="image_url" placeholder="https://..." @input="previewUrl($event)"
                                class="w-full h-8 px-2 border border-gray-900 rounded-lg text-xs focus:outline-none focus:border-red-600">
                        </div>
                        <!-- Preview -->
                        <div x-show="preview" x-cloak class="mt-1">
                            <img :src="preview" class="w-full h-16 object-cover rounded-lg border border-gray-900">
                        </div>
                    </div>

                    <!-- ── Tablet Banner ── -->
                    <div x-data="bannerUploader('tablet')"
                        class="bg-white dark:bg-slate-800 p-3 rounded-xl border border-gray-200 dark:border-slate-700 shadow-xs space-y-2">
                        <div class="flex items-center space-x-1.5 mb-1">
                            <i data-lucide="tablet" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                            <label class="text-xs font-semibold text-gray-800 dark:text-gray-200">Tablet Banner <span
                                    class="text-gray-400 dark:text-gray-500 font-normal">(768px–1024px)</span></label>
                        </div>
                        <div class="flex space-x-1.5">
                            <button type="button" @click="method='upload'"
                                :class="method==='upload' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300'"
                                class="h-6 px-2.5 rounded-md text-[10px] font-semibold transition flex items-center space-x-1">
                                <i data-lucide="upload" class="w-3 h-3"></i><span>Upload</span>
                            </button>
                            <button type="button" @click="method='url'"
                                :class="method==='url' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300'"
                                class="h-6 px-2.5 rounded-md text-[10px] font-semibold transition flex items-center space-x-1">
                                <i data-lucide="link" class="w-3 h-3"></i><span>URL</span>
                            </button>
                        </div>
                        <div x-show="method==='upload'" class="space-y-1">
                            <input type="file" name="tablet_image_file"
                                accept="image/jpeg,image/png,image/webp,image/gif" @change="previewFile($event)"
                                class="block w-full text-xs text-gray-600 dark:text-gray-300 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-red-50 dark:file:bg-red-950/60 file:text-red-700 dark:file:text-red-300 hover:file:bg-red-100 cursor-pointer">
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 flex items-center space-x-1">
                                <i data-lucide="ruler" class="w-3 h-3"></i><span>Rec: 900 × 450 px</span>
                            </p>
                        </div>
                        <div x-show="method==='url'" x-cloak>
                            <input type="url" name="tablet_image_url" placeholder="https://..."
                                @input="previewUrl($event)"
                                class="w-full h-8 px-2 border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-lg text-xs text-gray-900 dark:text-gray-100 focus:outline-none focus:border-red-600">
                        </div>
                        <div x-show="preview" x-cloak class="mt-1">
                            <img :src="preview" class="w-full h-16 object-cover rounded-lg border border-gray-200 dark:border-slate-700">
                        </div>
                    </div>

                    <!-- ── Mobile Banner ── -->
                    <div x-data="bannerUploader('mobile')"
                        class="bg-white dark:bg-slate-800 p-3 rounded-xl border border-gray-200 dark:border-slate-700 shadow-xs space-y-2">
                        <div class="flex items-center space-x-1.5 mb-1">
                            <i data-lucide="smartphone" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                            <label class="text-xs font-semibold text-gray-800 dark:text-gray-200">Mobile Banner <span
                                    class="text-gray-400 dark:text-gray-500 font-normal">(&lt;768px)</span></label>
                        </div>
                        <div class="flex space-x-1.5">
                            <button type="button" @click="method='upload'"
                                :class="method==='upload' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300'"
                                class="h-6 px-2.5 rounded-md text-[10px] font-semibold transition flex items-center space-x-1">
                                <i data-lucide="upload" class="w-3 h-3"></i><span>Upload</span>
                            </button>
                            <button type="button" @click="method='url'"
                                :class="method==='url' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-gray-300'"
                                class="h-6 px-2.5 rounded-md text-[10px] font-semibold transition flex items-center space-x-1">
                                <i data-lucide="link" class="w-3 h-3"></i><span>URL</span>
                            </button>
                        </div>
                        <div x-show="method==='upload'" class="space-y-1">
                            <input type="file" name="mobile_image_file"
                                accept="image/jpeg,image/png,image/webp,image/gif" @change="previewFile($event)"
                                class="block w-full text-xs text-gray-600 dark:text-gray-300 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[10px] file:font-semibold file:bg-red-50 dark:file:bg-red-950/60 file:text-red-700 dark:file:text-red-300 hover:file:bg-red-100 cursor-pointer">
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 flex items-center space-x-1">
                                <i data-lucide="ruler" class="w-3 h-3"></i><span>Rec: 500 × 300 px</span>
                            </p>
                        </div>
                        <div x-show="method==='url'" x-cloak>
                            <input type="url" name="mobile_image_url" placeholder="https://..."
                                @input="previewUrl($event)"
                                class="w-full h-8 px-2 border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-lg text-xs text-gray-900 dark:text-gray-100 focus:outline-none focus:border-red-600">
                        </div>
                        <div x-show="preview" x-cloak class="mt-1">
                            <img :src="preview" class="w-full h-16 object-cover rounded-lg border border-gray-200 dark:border-slate-700">
                        </div>
                    </div>

                </div><!-- /grid -->
            </div><!-- /device uploads -->

            <!-- Link & Sort -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Click Link (Optional)</label>
                    <input type="text" name="link_url" placeholder="e.g. /shop or https://..."
                        class="w-full h-10 px-3 border border-gray-900 rounded-xl text-xs focus:outline-none focus:border-red-600 focus:ring-1 focus:ring-red-500">
                    <p class="text-[11px] text-gray-400 mt-1">Leave blank = banner not clickable</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Sort Order <span
                            class="text-gray-400 font-normal">(lower = first)</span></label>
                    <input type="number" name="sort_order" value="0" min="0"
                        class="w-full h-10 px-3 border border-gray-900 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>
            </div>

            <!-- Active Toggle -->
            <div class="flex items-center space-x-3">
                <input type="checkbox" name="is_active" id="is_active_add" value="1" checked
                    class="w-4 h-4 accent-red-600 rounded">
                <label for="is_active_add" class="text-xs font-semibold text-gray-700">Active (show on homepage)</label>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="h-10 px-6 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition shadow-sm flex items-center space-x-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Add Banner</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Banner List -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-xs overflow-hidden" id="banner-list">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center space-x-2">
            <i data-lucide="layout-list" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">All Banners (<?= count($banners) ?>)</h2>
        </div>

        <?php if (empty($banners)): ?>
            <div class="p-12 text-center">
                <i data-lucide="image" class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-3"></i>
                <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">No banners yet. Add your first banner above!</p>
            </div>
        <?php else: ?>
            <!-- Size Reminder -->
            <div class="px-5 py-3 bg-amber-50 dark:bg-amber-950/60 border-b border-amber-100 dark:border-amber-900/60 flex items-center space-x-2">
                <i data-lucide="ruler" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0"></i>
                <p class="text-[11px] text-amber-800 dark:text-amber-300 font-semibold">Recommended banner size: <strong>1400 × 480 px</strong>
                    · Aspect ratio 2.9:1 · JPG/PNG/WEBP</p>
            </div>

            <div class="divide-y divide-gray-100" id="banners-container">
                <?php foreach ($banners as $banner): ?>
                    <?php
                    $src = \App\Models\Banner::getImageSrc($banner);
                    $tabletSrc = \App\Models\Banner::getTabletImageSrc($banner);
                    $mobileSrc = \App\Models\Banner::getMobileImageSrc($banner);
                    ?>
                    <div class="p-4 flex items-center gap-4 group hover:bg-gray-50 transition"
                        id="banner-row-<?= $banner['id'] ?>">

                        <!-- Preview Thumbnails -->
                        <div class="flex gap-2 shrink-0">
                            <!-- Desktop thumb -->
                            <div class="w-28 h-14 rounded-lg overflow-hidden border border-gray-900 bg-gray-100 flex items-center justify-center relative"
                                title="Desktop">
                                <?php if ($src): ?>
                                    <img src="<?= htmlspecialchars($src) ?>" alt="Desktop" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i data-lucide="image-off" class="w-5 h-5 text-gray-300"></i>
                                <?php endif; ?>
                                <span
                                    class="absolute bottom-0.5 left-0.5 text-[8px] bg-black/50 text-white px-1 rounded font-semibold">D</span>
                            </div>
                            <!-- Tablet thumb -->
                            <div class="w-14 h-14 rounded-lg overflow-hidden border border-gray-900 bg-gray-100 flex items-center justify-center relative"
                                title="Tablet">
                                <?php if ($tabletSrc): ?>
                                    <img src="<?= htmlspecialchars($tabletSrc) ?>" alt="Tablet" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i data-lucide="minus" class="w-4 h-4 text-gray-300"></i>
                                <?php endif; ?>
                                <span
                                    class="absolute bottom-0.5 left-0.5 text-[8px] bg-black/50 text-white px-1 rounded font-semibold">T</span>
                            </div>
                            <!-- Mobile thumb -->
                            <div class="w-10 h-14 rounded-lg overflow-hidden border border-gray-900 bg-gray-100 flex items-center justify-center relative"
                                title="Mobile">
                                <?php if ($mobileSrc): ?>
                                    <img src="<?= htmlspecialchars($mobileSrc) ?>" alt="Mobile" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i data-lucide="minus" class="w-4 h-4 text-gray-300"></i>
                                <?php endif; ?>
                                <span
                                    class="absolute bottom-0.5 left-0.5 text-[8px] bg-black/50 text-white px-1 rounded font-semibold">M</span>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0 space-y-1">
                            <p class="text-xs font-semibold text-gray-900 truncate">
                                <?= htmlspecialchars($banner['title'] ?: '(Untitled)') ?>
                            </p>
                            <?php if ($banner['link_url']): ?>
                                <p class="text-[11px] text-blue-600 truncate flex items-center space-x-1">
                                    <i data-lucide="link" class="w-3 h-3"></i>
                                    <span><?= htmlspecialchars($banner['link_url']) ?></span>
                                </p>
                            <?php endif; ?>
                            <div class="flex items-center space-x-3 flex-wrap gap-y-1">
                                <span class="text-[11px] text-gray-400">Order: <?= $banner['sort_order'] ?></span>
                                <!-- Device image status pills -->
                                <?php if (!empty($banner['image_path'])): ?>
                                    <span
                                        class="text-[10px] bg-red-50 text-red-700 px-1.5 py-0.5 rounded font-semibold flex items-center space-x-0.5">
                                        <i data-lucide="monitor" class="w-2.5 h-2.5"></i><span>Desktop</span>
                                    </span>
                                <?php elseif (!empty($banner['image_url'])): ?>
                                    <span
                                        class="text-[10px] bg-red-50 text-red-700 px-1.5 py-0.5 rounded font-semibold flex items-center space-x-0.5">
                                        <i data-lucide="link" class="w-2.5 h-2.5"></i><span>Desktop URL</span>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($banner['tablet_image_path']) || !empty($banner['tablet_image_url'])): ?>
                                    <span
                                        class="text-[10px] bg-red-50 text-red-700 px-1.5 py-0.5 rounded font-semibold flex items-center space-x-0.5">
                                        <i data-lucide="tablet" class="w-2.5 h-2.5"></i><span>Tablet</span>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($banner['mobile_image_path']) || !empty($banner['mobile_image_url'])): ?>
                                    <span
                                        class="text-[10px] bg-red-50 text-red-700 px-1.5 py-0.5 rounded font-semibold flex items-center space-x-0.5">
                                        <i data-lucide="smartphone" class="w-2.5 h-2.5"></i><span>Mobile</span>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="shrink-0">
                            <?php if ($banner['is_active']): ?>
                                <span
                                    class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">
                                    <i data-lucide="circle" class="w-2 h-2 fill-current"></i><span>Active</span>
                                </span>
                            <?php else: ?>
                                <span
                                    class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-500">
                                    <i data-lucide="circle" class="w-2 h-2 fill-current"></i><span>Hidden</span>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Actions -->
                        <div class="shrink-0 flex items-center space-x-2">
                            <a href="<?= url('admin/banners/toggle/' . $banner['id']) ?>"
                                class="h-8 px-3 rounded-lg text-[11px] font-semibold border border-gray-900 hover:border-red-600 text-gray-600 hover:text-red-600 transition flex items-center space-x-1">
                                <i data-lucide="<?= $banner['is_active'] ? 'eye-off' : 'eye' ?>" class="w-3.5 h-3.5"></i>
                                <span><?= $banner['is_active'] ? 'Hide' : 'Show' ?></span>
                            </a>
                            <a href="<?= url('admin/banners/edit/' . $banner['id']) ?>"
                                class="h-8 px-3 rounded-lg text-[11px] font-semibold bg-gray-100 hover:bg-gray-900 text-gray-700 transition flex items-center space-x-1">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                <span>Edit</span>
                            </a>
                            <button type="button"
                                onclick="confirmDeleteBanner(<?= $banner['id'] ?>, '<?= htmlspecialchars(addslashes($banner['title'] ?: 'Untitled'), ENT_QUOTES) ?>')"
                                class="h-8 px-3 rounded-lg text-[11px] font-semibold bg-red-50 hover:bg-red-600 text-red-600 hover:text-white transition flex items-center space-x-1">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                <span>Delete</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ── Delete Confirmation Modal ── -->
<div id="delete-modal"
    class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-[99999] flex items-center justify-center p-3 sm:p-6 overflow-y-auto hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0" onclick="closeBannerDeleteModal()"></div>
    <!-- Dialog -->
    <div
        class="relative z-[100000] bg-white rounded-2xl border border-gray-900 shadow-2xl w-full max-w-sm p-6 space-y-4 my-auto max-h-[90vh] sm:max-h-[85vh] overflow-y-auto flex flex-col">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                <i data-lucide="trash-2" class="w-5 h-5 text-red-600"></i>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Delete Banner?</h3>
                <p class="text-xs text-gray-500 mt-0.5">This will permanently delete the banner and all its images.</p>
            </div>
        </div>
        <p class="text-xs text-gray-700 bg-gray-50 rounded-xl px-3 py-2 border border-gray-900">
            Banner: <strong id="delete-banner-title" class="text-gray-900"></strong>
        </p>
        <p class="text-[11px] text-red-600 font-semibold flex items-center space-x-1">
            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
            <span>This action cannot be undone.</span>
        </p>
        <div class="flex items-center justify-end space-x-3 pt-1">
            <button type="button" onclick="closeBannerDeleteModal()"
                class="h-9 px-4 bg-gray-100 hover:bg-gray-900 text-gray-700 text-xs font-semibold rounded-xl transition">
                Cancel
            </button>
            <button type="button" id="confirm-delete-btn"
                class="h-9 px-5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition flex items-center space-x-2">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                <span>Delete Banner</span>
            </button>
        </div>
    </div>
</div>

<script>
    // ── Banner Uploader Alpine component ──────────────────────────────────────
    function bannerUploader(device) {
        return {
            method: 'upload',
            preview: null,
            previewFile(e) {
                const file = e.target.files[0];
                if (!file) { this.preview = null; return; }
                const reader = new FileReader();
                reader.onload = (ev) => { this.preview = ev.target.result; };
                reader.readAsDataURL(file);
            },
            previewUrl(e) {
                const val = e.target.value.trim();
                this.preview = val || null;
            }
        };
    }

    // ── Delete Modal ──────────────────────────────────────────────────────────
    let _deleteBannerId = null;

    function confirmDeleteBanner(id, title) {
        _deleteBannerId = id;
        document.getElementById('delete-banner-title').textContent = title || 'Untitled';
        document.getElementById('delete-modal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeBannerDeleteModal() {
        _deleteBannerId = null;
        document.getElementById('delete-modal').classList.add('hidden');
    }

    document.getElementById('confirm-delete-btn').addEventListener('click', function () {
        if (!_deleteBannerId) return;

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span>Deleting…</span>';

        // Build a hidden CSRF-safe POST form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= url('admin/banners/delete') ?>/' + _deleteBannerId;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_csrf_token';
        csrf.value = '<?= csrf_token() ?>';
        form.appendChild(csrf);

        document.body.appendChild(form);
        form.submit();
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>