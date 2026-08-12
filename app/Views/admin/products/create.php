<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-5xl mx-auto space-y-6 font-sans pb-12">
    <!-- Header Bar -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-gray-900 shadow-xs">
        <div class="flex items-center space-x-3">
            <a href="<?= url('admin/products') ?>"
                class="w-10 h-10 bg-gray-100 text-gray-700 hover:bg-gray-900 hover:text-white rounded-xl transition flex items-center justify-center shrink-0"
                title="Back to Products List">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 leading-snug">Add New Accessory Product</h2>
                <p class="text-xs text-gray-500 mt-0.5 font-medium">Create product listing with pricing, gallery images,
                    categories &amp; brand assignments</p>
            </div>
        </div>
    </div>

    <form action="<?= url('admin/products/store') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <?= csrf_field() ?>

        <!-- SECTION 1: BASIC INFORMATION -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-5 shadow-xs">
            <div class="flex items-center space-x-2 border-b border-gray-100 pb-3">
                <i data-lucide="package" class="w-4 h-4 text-red-600"></i>
                <h3 class="font-semibold text-sm text-gray-900">Basic Information &amp; Pricing</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Product Name *</label>
                    <input type="text" name="name" required
                        placeholder="e.g. Mudsor Heavy-Duty Portable Fast Charger Dock"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl focus:outline-none focus:border-red-600 font-semibold text-gray-900">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">SKU Number *</label>
                    <input type="text" name="sku" required placeholder="MUD-CHG-001"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl uppercase font-mono font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Primary Category *</label>
                    <select name="category_id" required
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:bg-white text-xs focus:outline-none focus:border-red-600">
                        <option value="">-- Select Primary Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars(htmlspecialchars_decode($cat['name'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Primary Scooter Brand</label>
                    <select name="brand_id"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:bg-white text-xs focus:outline-none focus:border-red-600">
                        <option value="">-- Select Primary Brand --</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars(htmlspecialchars_decode($b['name'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Regular Price (₹) *</label>
                    <input type="number" step="0.01" name="price" required placeholder="999"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Sale Discounted Price (₹)</label>
                    <input type="number" step="0.01" name="sale_price" placeholder="449"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Inventory Stock Units *</label>
                    <input type="number" name="stock" required value="100" min="0"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Status</label>
                    <select name="status"
                        class="w-full h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">HSN Code</label>
                    <input type="text" name="hsn_code" value="8714.99.90" placeholder="e.g. 8714.99.90"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-mono font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">GST Rate (%) *</label>
                    <select name="tax_percent" required
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:bg-white text-xs focus:outline-none focus:border-red-600">
                        <option value="0">0% (Exempt / Nil Rated)</option>
                        <option value="5">5% GST</option>
                        <option value="12">12% GST</option>
                        <option value="18" selected>18% GST (Default)</option>
                        <option value="28">28% GST</option>
                    </select>
                </div>
            </div>
            <p class="text-[11px] text-gray-500 font-medium italic mt-2">
                * All product prices on Mudsor are treated as <strong>GST INCLUSIVE</strong>. GST % is used to calculate base price and GST breakdown for tax invoices.
            </p>
        </div>

        <!-- SECTION 1B: WARRANTY & OEM COMPARISON DATA -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-5 shadow-xs">
            <div class="flex items-center space-x-2 border-b border-gray-100 pb-3">
                <i data-lucide="git-compare" class="w-4 h-4 text-red-600"></i>
                <h3 class="font-semibold text-sm text-gray-900">Warranty &amp; OEM Comparison Data</h3>
                <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded font-semibold ml-auto">Shown in Compare Page</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                <!-- Mudsor Warranty -->
                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Mudsor Warranty (Months)</label>
                    <input type="number" name="warranty_months" id="create_warranty_months"
                        value="12" min="0" max="120" placeholder="e.g. 12"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                    <p class="text-[10px] text-gray-500 mt-1">Mudsor product ki warranty (months mein)</p>
                </div>

                <!-- OEM Price -->
                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">OEM / Company Price (₹)
                        <span class="text-gray-400 font-normal normal-case">(vehicle brand ka price)</span>
                    </label>
                    <input type="number" step="0.01" name="oem_price" id="create_oem_price"
                        value="" placeholder="Auto-estimated if blank"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                    <p class="text-[10px] text-gray-500 mt-1">Blank raho to auto-estimate (price × 1.4x) hoga</p>
                </div>

                <!-- OEM Warranty -->
                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">OEM Warranty (Months)</label>
                    <input type="number" name="oem_warranty_months" id="create_oem_warranty"
                        value="6" min="0" max="60" placeholder="e.g. 6"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                    <p class="text-[10px] text-gray-500 mt-1">Vehicle brand ki warranty (usually 3–12 months)</p>
                </div>

                <!-- OEM Material -->
                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">OEM Material</label>
                    <input type="text" name="oem_material"
                        value="Standard Steel / Plastic" placeholder="e.g. Standard Steel / Plastic"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <!-- Live Preview -->
                <div class="sm:col-span-2 bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <p class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider mb-2">🏆 Compare Page Preview (Live)</p>
                    <div class="grid grid-cols-3 text-[11px] font-semibold gap-2">
                        <div class="text-gray-500">Mudsor Price: <span class="text-gray-900" id="c_prev_mudsor">Enter price above</span></div>
                        <div class="text-gray-500">OEM Price: <span class="text-emerald-700" id="c_prev_oem">auto</span></div>
                        <div class="text-emerald-700" id="c_prev_saving">—</div>
                    </div>
                    <div class="grid grid-cols-2 text-[11px] font-semibold gap-2 mt-1.5">
                        <div class="text-gray-500">Mudsor Warranty: <span class="text-gray-900" id="c_prev_mw">12 Months</span></div>
                        <div class="text-gray-500">OEM Warranty: <span class="text-gray-900" id="c_prev_ow">6 Months</span></div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function() {
            function getCreateMudsorPrice() {
                var sp = parseFloat(document.querySelector('[name="sale_price"]')?.value) || 0;
                var p  = parseFloat(document.querySelector('[name="price"]')?.value) || 0;
                return sp > 0 ? sp : p;
            }
            function updateCreatePreview() {
                var mudsorPrice = getCreateMudsorPrice();
                var oemVal = parseFloat(document.getElementById('create_oem_price').value) || 0;
                var oemPrice = oemVal > 0 ? oemVal : (mudsorPrice > 0 ? Math.round(mudsorPrice * 1.4) : 0);
                var saving = oemPrice - mudsorPrice;
                var savingPct = oemPrice > 0 ? Math.round((saving / oemPrice) * 100) : 0;
                document.getElementById('c_prev_mudsor').textContent = mudsorPrice > 0 ? '₹' + Math.round(mudsorPrice).toLocaleString('en-IN') : 'Enter price above';
                document.getElementById('c_prev_oem').textContent = oemPrice > 0 ? '₹' + Math.round(oemPrice).toLocaleString('en-IN') + (oemVal <= 0 ? ' (est.)' : '') : '—';
                document.getElementById('c_prev_saving').textContent = saving > 0 && mudsorPrice > 0
                    ? '✅ Save ' + savingPct + '% (₹' + Math.round(saving).toLocaleString('en-IN') + ')'
                    : '—';
                var mw = document.getElementById('create_warranty_months').value;
                var ow = document.getElementById('create_oem_warranty').value;
                document.getElementById('c_prev_mw').textContent = mw ? mw + ' Months' : '—';
                document.getElementById('c_prev_ow').textContent = ow ? ow + ' Months' : '—';
            }
            ['create_oem_price', 'create_warranty_months', 'create_oem_warranty'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) el.addEventListener('input', updateCreatePreview);
            });
            document.querySelectorAll('[name="price"],[name="sale_price"]').forEach(function(el) {
                el.addEventListener('input', updateCreatePreview);
            });
            updateCreatePreview();
        })();
        </script>

        <!-- SECTION 2: CATEGORIES & BRANDS -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-5 shadow-xs">
            <div class="flex items-center space-x-2 border-b border-gray-100 pb-3">
                <i data-lucide="tag" class="w-4 h-4 text-red-600"></i>
                <h3 class="font-semibold text-sm text-gray-900">Categories &amp; Compatibility Mapping</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                <!-- MULTIPLE CATEGORIES -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-900 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="font-semibold text-gray-900 text-xs uppercase tracking-wider">Product
                            Categories</label>
                        <span
                            class="text-[10px] bg-red-600 text-white px-2 py-0.5 rounded font-semibold">MULTI-SELECT</span>
                    </div>
                    <div
                        class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 bg-white rounded-lg border border-gray-900">
                        <?php foreach ($categories as $cat): ?>
                            <label
                                class="flex items-center space-x-2 p-1.5 hover:bg-gray-50 rounded cursor-pointer font-semibold text-gray-800">
                                <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>"
                                    class="rounded text-red-600 focus:ring-red-500">
                                <span class="truncate"><?= htmlspecialchars(htmlspecialchars_decode($cat['name'])) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- MULTIPLE BRANDS -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-900 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="font-semibold text-gray-900 text-xs uppercase tracking-wider">Scooter
                            Brands</label>
                        <span
                            class="text-[10px] bg-gray-900 text-white px-2 py-0.5 rounded font-semibold">MULTI-SELECT</span>
                    </div>
                    <div
                        class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 bg-white rounded-lg border border-gray-900">
                        <?php foreach ($brands as $b): ?>
                            <label
                                class="flex items-center space-x-2 p-1.5 hover:bg-gray-50 rounded cursor-pointer font-semibold text-gray-800">
                                <input type="checkbox" name="brands[]" value="<?= $b['id'] ?>"
                                    class="rounded text-red-600 focus:ring-red-500">
                                <span class="truncate"><?= htmlspecialchars(htmlspecialchars_decode($b['name'])) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: MAIN PRODUCT IMAGE -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-4 shadow-xs" x-data="{ imgMode: 'url' }">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center space-x-2">
                    <i data-lucide="image" class="w-4 h-4 text-red-600"></i>
                    <h3 class="font-semibold text-sm text-gray-900">Main Product Image *</h3>
                </div>
                <div class="flex bg-gray-100 p-1 rounded-lg text-xs font-semibold space-x-1">
                    <button type="button" @click="imgMode = 'url'"
                        :class="imgMode === 'url' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                        class="px-3 py-1 rounded-md transition">Image Link / URL</button>
                    <button type="button" @click="imgMode = 'upload'"
                        :class="imgMode === 'upload' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                        class="px-3 py-1 rounded-md transition">Upload File</button>
                </div>
            </div>

            <div x-show="imgMode === 'url'" class="space-y-1">
                <input type="url" name="main_image_url" placeholder="https://example.com/image.jpg"
                    class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-red-600">
                <p class="text-[11px] text-gray-500">Direct image URL (JPEG, PNG, WEBP)</p>
            </div>

            <div x-show="imgMode === 'upload'" class="space-y-1" x-cloak>
                <input type="file" name="main_image" accept="image/*"
                    class="w-full p-2 bg-gray-50 border border-gray-900 rounded-xl text-xs">
                <p class="text-[11px] text-gray-500">Upload main product image file (Max 5MB)</p>
            </div>
        </div>

        <!-- SECTION 4: PRODUCT GALLERY IMAGES -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-4 shadow-xs">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center space-x-2">
                    <i data-lucide="images" class="w-4 h-4 text-red-600"></i>
                    <h3 class="font-semibold text-sm text-gray-900">Additional Gallery Images</h3>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="space-y-1">
                    <label class="block font-semibold text-gray-700">Upload Multiple Image Files:</label>
                    <input type="file" name="gallery_images[]" accept="image/*" multiple
                        class="w-full p-2.5 bg-gray-50 border border-gray-900 rounded-xl text-xs">
                    <p class="text-[11px] text-gray-500">Select multiple product photos from your computer.</p>
                </div>
                <div class="space-y-1">
                    <label class="block font-semibold text-gray-700">Or Paste Image URLs (one per line):</label>
                    <textarea name="gallery_urls" rows="2"
                        placeholder="https://example.com/photo1.jpg&#10;https://example.com/photo2.jpg"
                        class="w-full p-2.5 bg-gray-50 border border-gray-900 rounded-xl text-xs font-mono focus:outline-none focus:border-red-600"></textarea>
                    <p class="text-[11px] text-gray-500">Enter direct image URLs.</p>
                </div>
        </div>

        <!-- SECTION: PRODUCT DEMO VIDEO & COVER THUMBNAIL -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-4 shadow-xs" x-data="{ videoSource: 'url' }">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center space-x-2">
                    <i data-lucide="video" class="w-4 h-4 text-red-600"></i>
                    <h3 class="font-semibold text-sm text-gray-900">Product Video &amp; Demo Media (Optional)</h3>
                </div>
                <div class="flex bg-gray-100 p-1 rounded-lg text-xs font-semibold space-x-1">
                    <button type="button" @click="videoSource = 'url'"
                        :class="videoSource === 'url' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                        class="px-3 py-1 rounded-md transition">Video Link / URL</button>
                    <button type="button" @click="videoSource = 'upload'"
                        :class="videoSource === 'upload' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                        class="px-3 py-1 rounded-md transition">Upload Video File</button>
                </div>
            </div>

            <!-- Hidden input for client-side HTML5 Canvas extracted frame base64 fallback -->
            <input type="hidden" name="auto_video_thumbnail_base64" id="create-auto-thumb-base64">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                <!-- Video Source Field -->
                <div class="space-y-2">
                    <div x-show="videoSource === 'url'" class="space-y-1">
                        <label class="block font-semibold text-gray-700 uppercase">Video URL Link</label>
                        <input type="url" name="video_url"
                            placeholder="https://www.youtube.com/watch?v=... or MP4 URL"
                            class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-red-600">
                        <p class="text-[11px] text-gray-500">YouTube, Instagram, Facebook, or MP4 link</p>
                    </div>

                    <div x-show="videoSource === 'upload'" class="space-y-1" x-cloak>
                        <label class="block font-semibold text-gray-700 uppercase">Select Video File (.mp4, .webm)</label>
                        <input type="file" name="video_file" accept="video/mp4,video/webm"
                            onchange="extractFrameThumbnail(this, 'create')"
                            class="w-full p-2 bg-gray-50 border border-gray-900 rounded-xl text-xs">
                        <p class="text-[11px] text-gray-500">Auto-extracts frame at 1-sec mark for poster thumbnail if no custom cover photo is uploaded.</p>
                    </div>
                </div>

                <!-- Cover Photo (Manual Override & Auto-Preview) -->
                <div class="space-y-2">
                    <label class="block font-semibold text-gray-700 uppercase">Cover Photo / Video Poster (Manual Override)</label>
                    <input type="file" name="video_thumbnail" accept="image/*"
                        class="w-full p-2 bg-gray-50 border border-gray-900 rounded-xl text-xs">
                    <p class="text-[11px] text-gray-500">Optional custom cover image. Overrides auto-generated video frame thumbnail.</p>

                    <!-- Effective Cover Preview Display -->
                    <div id="create-poster-preview-box" class="pt-2 flex items-center space-x-3 hidden">
                        <div class="relative w-24 aspect-video rounded-lg overflow-hidden bg-black border border-gray-300 shrink-0">
                            <img id="create-poster-preview-img" src="" class="w-full h-full object-cover">
                            <span class="absolute bottom-0.5 right-0.5 text-[8px] bg-black/80 text-white font-bold px-1 rounded">POSTER</span>
                        </div>
                        <div class="text-[11px] text-gray-500 font-medium">
                            <span id="create-poster-type-label" class="font-semibold text-gray-800">⚡ Auto-Extracted Frame Preview</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function extractFrameThumbnail(fileInput, mode) {
                if (!fileInput.files || !fileInput.files[0]) return;
                const file = fileInput.files[0];
                if (!file.type.startsWith('video/')) return;

                const video = document.createElement('video');
                video.preload = 'metadata';
                video.src = URL.createObjectURL(file);
                video.muted = true;
                video.playsInline = true;

                video.onloadeddata = function() {
                    video.currentTime = Math.min(1.0, (video.duration || 2) / 2);
                };

                video.onseeked = function() {
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth || 640;
                    canvas.height = video.videoHeight || 360;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const base64Data = canvas.toDataURL('image/jpeg', 0.85);
                    const hiddenInput = document.getElementById(mode + '-auto-thumb-base64');
                    if (hiddenInput) hiddenInput.value = base64Data;

                    const previewImg = document.getElementById(mode + '-poster-preview-img');
                    const previewBox = document.getElementById(mode + '-poster-preview-box');

                    if (previewImg) {
                        previewImg.src = base64Data;
                    }
                    if (previewBox) {
                        previewBox.classList.remove('hidden');
                    }

                    URL.revokeObjectURL(video.src);
                };
            }
        </script>

        <!-- SECTION 5: PROMOTIONAL BADGES & DESCRIPTION -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-5 shadow-xs">
            <div class="flex items-center space-x-2 border-b border-gray-100 pb-3">
                <i data-lucide="file-text" class="w-4 h-4 text-red-600"></i>
                <h3 class="font-semibold text-sm text-gray-900">Description &amp; Badges</h3>
            </div>

            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-red-600 uppercase mb-2">Promotional Badges</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <label
                            class="flex items-center space-x-2 bg-gray-50 p-3 rounded-xl border border-gray-900 cursor-pointer font-semibold hover:border-red-600 transition">
                            <input type="checkbox" name="is_featured" value="1"
                                class="rounded text-red-600 focus:ring-red-500 w-4 h-4">
                            <span>Featured Deal</span>
                        </label>
                        <label
                            class="flex items-center space-x-2 bg-gray-50 p-3 rounded-xl border border-gray-900 cursor-pointer font-semibold hover:border-red-600 transition">
                            <input type="checkbox" name="is_best_seller" value="1"
                                class="rounded text-red-600 focus:ring-red-500 w-4 h-4">
                            <span>Best Seller</span>
                        </label>
                        <label
                            class="flex items-center space-x-2 bg-gray-50 p-3 rounded-xl border border-gray-900 cursor-pointer font-semibold hover:border-red-600 transition">
                            <input type="checkbox" name="is_new_arrival" value="1"
                                class="rounded text-red-600 focus:ring-red-500 w-4 h-4">
                            <span>New Arrival</span>
                        </label>
                        <label
                            class="flex items-center space-x-2 bg-gray-50 p-3 rounded-xl border border-gray-900 cursor-pointer font-semibold hover:border-red-600 transition">
                            <input type="checkbox" name="is_flash_sale" value="1"
                                class="rounded text-red-600 focus:ring-red-500 w-4 h-4">
                            <span>Flash Sale</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Product Description</label>
                    <textarea name="description" rows="5"
                        placeholder="Detailed product specifications, compatibility guidance, and features..."
                        class="w-full p-4 bg-gray-50 border border-gray-900 rounded-xl focus:outline-none focus:border-red-600 text-xs font-medium text-gray-900"></textarea>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end pt-2">
            <button type="submit"
                class="h-12 px-10 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-md flex items-center space-x-2 cursor-pointer">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <span>Save &amp; Publish Product</span>
            </button>
        </div>
    </form>
</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>