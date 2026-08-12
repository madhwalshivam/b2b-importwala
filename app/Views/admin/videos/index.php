<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="p-6 max-w-7xl mx-auto space-y-6 font-sans">

    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Media Center
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Homepage Video Showcase</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Homepage Videos Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Add YouTube, Instagram Reels/Posts, Facebook Video URLs, or upload custom video files to show product demos and testing videos on the storefront homepage.
            </p>
        </div>

        <button type="button" onclick="openAddVideoModal()"
            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-xs flex items-center space-x-2 cursor-pointer">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add New Video</span>
        </button>
    </div>

    <!-- Video Grid / List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-900 flex items-center space-x-2">
                <i data-lucide="video" class="w-5 h-5 text-red-600"></i>
                <span>Active Homepage Videos (<?= count($videos) ?>)</span>
            </h3>
        </div>

        <?php if (empty($videos)): ?>
            <div class="p-12 text-center space-y-3">
                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto">
                    <i data-lucide="video-off" class="w-8 h-8"></i>
                </div>
                <p class="text-sm font-semibold text-slate-700">No Homepage Videos Added Yet</p>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">Add your YouTube, Instagram, Facebook, or custom video files to demonstrate product features.</p>
                <button type="button" onclick="openAddVideoModal()"
                    class="px-4 py-2 bg-red-600 text-white text-xs font-semibold rounded-xl hover:bg-red-700 transition">
                    + Add First Video
                </button>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                <?php foreach ($videos as $v): ?>
                    <?php 
                        $vType = strtolower($v['video_type'] ?? 'youtube');
                        $typeBadgeClass = 'bg-slate-800 text-white';
                        if ($vType === 'youtube') $typeBadgeClass = 'bg-red-600 text-white';
                        elseif ($vType === 'instagram') $typeBadgeClass = 'bg-gradient-to-r from-purple-600 to-pink-600 text-white';
                        elseif ($vType === 'facebook') $typeBadgeClass = 'bg-blue-600 text-white';
                    ?>
                    <div class="bg-slate-50 rounded-2xl border border-slate-200 overflow-hidden flex flex-col justify-between group hover:border-slate-400 transition shadow-xs">
                        <div class="relative aspect-video bg-black overflow-hidden flex items-center justify-center">
                            <?php if (!empty($v['thumbnail'])): ?>
                                <img src="<?= asset($v['thumbnail']) ?>"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300 opacity-90">
                            <?php else: ?>
                                <div class="w-full h-full bg-slate-900 flex items-center justify-center text-slate-500">
                                    <i data-lucide="video" class="w-12 h-12"></i>
                                </div>
                            <?php endif; ?>

                            <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                                <a href="<?= htmlspecialchars($v['video_url']) ?>" target="_blank"
                                    class="w-12 h-12 rounded-full bg-red-600/90 text-white flex items-center justify-center shadow-lg hover:scale-110 transition"
                                    title="View Original Video">
                                    <i data-lucide="play" class="w-6 h-6 fill-white ml-0.5"></i>
                                </a>
                            </div>

                            <span class="absolute top-3 left-3 text-[10px] font-bold uppercase px-2 py-0.5 rounded-md tracking-wider shadow-sm <?= $typeBadgeClass ?>">
                                <?= htmlspecialchars(strtoupper($vType)) ?>
                            </span>
                        </div>

                        <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                            <div>
                                <h4 class="text-sm font-semibold text-slate-900 line-clamp-1 leading-snug">
                                    <?= htmlspecialchars($v['title']) ?>
                                </h4>
                                <p class="text-xs text-slate-500 line-clamp-2 mt-1 font-medium">
                                    <?= htmlspecialchars($v['description'] ?: 'No description provided.') ?>
                                </p>
                            </div>

                            <div class="pt-3 border-t border-slate-200/60 flex items-center justify-between">
                                <span class="text-[11px] font-mono text-slate-400">Order: #<?= (int) $v['display_order'] ?></span>
                                <div class="flex items-center space-x-2">
                                    <button type="button" onclick='openEditVideoModal(<?= json_encode($v, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' 
                                        class="p-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer" title="Edit Video">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    <a href="<?= url('admin/videos/delete/' . $v['id']) ?>"
                                        data-confirm="Are you sure you want to delete this video?"
                                        class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition"
                                        title="Delete Video">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ADD VIDEO MODAL -->
<div id="add-video-modal" class="hidden fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-[99999] flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 border border-slate-200 my-auto z-[100000] max-h-[90vh] sm:max-h-[85vh] overflow-y-auto flex flex-col">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-semibold text-slate-900 flex items-center space-x-2">
                <i data-lucide="video" class="w-5 h-5 text-red-600"></i>
                <span>Add Homepage Video</span>
            </h3>
            <button type="button" onclick="closeAddVideoModal()" class="text-slate-400 hover:text-slate-700 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="<?= url('admin/videos/store') ?>" method="POST" enctype="multipart/form-data" onsubmit="return validateVideoForm(this, 'add')" class="space-y-4">
            <?= csrf_field() ?>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase">Video Title *</label>
                <input type="text" name="title" placeholder="e.g. Mudsor Heavy Stainless Steel Crash Guard Testing" required
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
            </div>

            <!-- SOURCE TYPE SELECTOR -->
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase">Source Type *</label>
                <select name="video_type" id="add-video-type" onchange="toggleVideoInputs(this.value, 'add')"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                    <option value="link" selected>Paste Video Link (YouTube / Instagram / Facebook)</option>
                    <option value="upload">Upload Video File (.mp4 / .webm)</option>
                </select>
            </div>

            <!-- UNIVERSAL LINK INPUT GROUP -->
            <div id="add-link-input-group" class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-slate-700 uppercase">Paste Video Link *</label>
                    <span id="add-platform-badge" class="hidden text-[10px] font-bold px-2 py-0.5 rounded shadow-2xs"></span>
                </div>
                <input type="url" name="video_url" id="add-video-url" oninput="previewVideoLink('add')"
                    placeholder="https://www.youtube.com/watch?v=... or Instagram Reel / Facebook Video link"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                <p class="text-[11px] text-slate-500 font-medium">Supports YouTube (videos/shorts), Instagram (reels/posts), and Facebook videos.</p>

                <!-- URL Error Message -->
                <div id="add-url-error" class="hidden text-xs text-red-600 bg-red-50 p-2.5 rounded-xl border border-red-200 font-semibold">
                    ⚠️ Please enter a valid YouTube, Instagram, or Facebook video link
                </div>

                <!-- LIVE EMBED PREVIEW BOX -->
                <div id="add-preview-container" class="hidden space-y-1.5 pt-1">
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase">Live Embed Preview</label>
                    <div class="relative aspect-video bg-black rounded-xl overflow-hidden border border-slate-300 shadow-sm">
                        <iframe id="add-preview-iframe" class="w-full h-full" src="" frameborder="0" allowfullscreen allow="autoplay"></iframe>
                    </div>
                </div>
            </div>

            <!-- FILE UPLOAD GROUP -->
            <div id="add-upload-input-group" class="space-y-1 hidden">
                <label class="block text-xs font-semibold text-slate-700 uppercase">Select Video File (.mp4, .webm)</label>
                <input type="file" name="video_file" accept="video/mp4,video/webm"
                    class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700">
            </div>

            <!-- Thumbnail Upload (Optional) -->
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase">Thumbnail Image (Optional)</label>
                <input type="file" name="thumbnail" accept="image/*"
                    class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700">
                <p class="text-[10px] text-slate-400 italic">Auto-generated for YouTube if left blank.</p>
            </div>

            <!-- Showcase Product Link (Optional) -->
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase flex items-center justify-between">
                    <span>Showcase Product Link (Optional)</span>
                    <span class="text-[10px] text-red-600 font-medium lowercase">e.g. /product/mudsor-9h-tempered-glass</span>
                </label>
                <input type="text" name="product_url" placeholder="e.g. /product/scooter-crash-guard or https://..."
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase">Short Description (Optional)</label>
                <textarea name="description" rows="2" placeholder="Brief caption describing what this video demonstrates..."
                    class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition"></textarea>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase">Sort Order</label>
                <input type="number" name="display_order" value="1" min="0"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900">
            </div>

            <div class="pt-3 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeAddVideoModal()"
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                    Save Video
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT VIDEO MODAL -->
<div id="edit-video-modal" class="hidden fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-[99999] flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 border border-slate-200 my-auto z-[100000] max-h-[90vh] sm:max-h-[85vh] overflow-y-auto flex flex-col">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-semibold text-slate-900 flex items-center space-x-2">
                <i data-lucide="pencil" class="w-5 h-5 text-red-600"></i>
                <span>Edit Homepage Video</span>
            </h3>
            <button type="button" onclick="closeEditVideoModal()" class="text-slate-400 hover:text-slate-700 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="edit-video-form" action="" method="POST" enctype="multipart/form-data" onsubmit="return validateVideoForm(this, 'edit')" class="space-y-4">
            <?= csrf_field() ?>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase">Video Title *</label>
                <input type="text" name="title" id="edit-video-title" required
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
            </div>

            <!-- SOURCE TYPE SELECTOR -->
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase">Source Type *</label>
                <select name="video_type" id="edit-video-type" onchange="toggleVideoInputs(this.value, 'edit')"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                    <option value="link">Paste Video Link (YouTube / Instagram / Facebook)</option>
                    <option value="upload">Upload Video File (.mp4 / .webm)</option>
                </select>
            </div>

            <!-- UNIVERSAL LINK INPUT GROUP -->
            <div id="edit-link-input-group" class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-slate-700 uppercase">Paste Video Link *</label>
                    <span id="edit-platform-badge" class="hidden text-[10px] font-bold px-2 py-0.5 rounded shadow-2xs"></span>
                </div>
                <input type="url" name="video_url" id="edit-video-url" oninput="previewVideoLink('edit')"
                    placeholder="https://www.youtube.com/watch?v=... or Instagram Reel / Facebook Video link"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                
                <!-- URL Error Message -->
                <div id="edit-url-error" class="hidden text-xs text-red-600 bg-red-50 p-2.5 rounded-xl border border-red-200 font-semibold">
                    ⚠️ Please enter a valid YouTube, Instagram, or Facebook video link
                </div>

                <!-- LIVE EMBED PREVIEW BOX -->
                <div id="edit-preview-container" class="hidden space-y-1.5 pt-1">
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase">Live Embed Preview</label>
                    <div class="relative aspect-video bg-black rounded-xl overflow-hidden border border-slate-300 shadow-sm">
                        <iframe id="edit-preview-iframe" class="w-full h-full" src="" frameborder="0" allowfullscreen allow="autoplay"></iframe>
                    </div>
                </div>
            </div>

            <!-- FILE UPLOAD GROUP -->
            <div id="edit-upload-input-group" class="space-y-1 hidden">
                <label class="block text-xs font-semibold text-slate-700 uppercase">Select New Video File (.mp4, .webm - Optional)</label>
                <input type="file" name="video_file" accept="video/mp4,video/webm"
                    class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700">
            </div>

            <!-- Thumbnail Upload (Optional) -->
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase">New Thumbnail Image (Optional)</label>
                <input type="file" name="thumbnail" accept="image/*"
                    class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700">
            </div>

            <!-- Showcase Product Link (Optional) -->
            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase flex items-center justify-between">
                    <span>Showcase Product Link (Optional)</span>
                    <span class="text-[10px] text-red-600 font-medium lowercase">e.g. /product/mudsor-9h-tempered-glass</span>
                </label>
                <input type="text" name="product_url" id="edit-product-url" placeholder="e.g. /product/scooter-crash-guard or https://..."
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase">Short Description</label>
                <textarea name="description" id="edit-video-description" rows="2"
                    class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition"></textarea>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase">Sort Order</label>
                <input type="number" name="display_order" id="edit-video-order" min="0"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900">
            </div>

            <div class="pt-3 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="closeEditVideoModal()"
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                    Update Video
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Universal URL Parser for YouTube, Instagram, Facebook
    function parseVideoUrl(url) {
        url = (url || '').trim();
        if (!url) return { platform: 'empty', embedUrl: '', isValid: true, badge: '' };

        // 1. YouTube Regex (watch, Shorts, youtu.be, embed)
        const ytMatch = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i);
        if (ytMatch && ytMatch[1]) {
            return {
                platform: 'youtube',
                embedUrl: 'https://www.youtube.com/embed/' + ytMatch[1] + '?autoplay=0',
                isValid: true,
                badge: 'YouTube',
                badgeClass: 'bg-red-600 text-white'
            };
        }

        // 2. Instagram Regex (Reel, Post, TV)
        const igMatch = url.match(/(?:instagram\.com|instagr\.am)\/(?:p|reel|tv)\/([a-zA-Z0-9_\-]+)/i);
        if (igMatch && igMatch[1]) {
            return {
                platform: 'instagram',
                embedUrl: 'https://www.instagram.com/p/' + igMatch[1] + '/embed/',
                isValid: true,
                badge: 'Instagram Reel/Post',
                badgeClass: 'bg-gradient-to-r from-purple-600 to-pink-600 text-white'
            };
        }

        // 3. Facebook Regex (Video, Watch, Reel, Share)
        const fbMatch = url.match(/(?:facebook\.com\/(?:.*\/videos\/|video\.php|watch|reel|share\/)|fb\.watch\/)/i);
        if (fbMatch) {
            return {
                platform: 'facebook',
                embedUrl: 'https://www.facebook.com/plugins/video.php?href=' + encodeURIComponent(url) + '&show_text=0',
                isValid: true,
                badge: 'Facebook Video',
                badgeClass: 'bg-blue-600 text-white'
            };
        }

        return {
            platform: 'unknown',
            embedUrl: '',
            isValid: false,
            badge: 'Invalid Link',
            badgeClass: 'bg-red-100 text-red-700 border border-red-300'
        };
    }

    function previewVideoLink(mode) {
        const input = document.getElementById(mode + '-video-url');
        const badge = document.getElementById(mode + '-platform-badge');
        const errorBox = document.getElementById(mode + '-url-error');
        const previewContainer = document.getElementById(mode + '-preview-container');
        const iframe = document.getElementById(mode + '-preview-iframe');

        if (!input) return;

        const res = parseVideoUrl(input.value);

        if (res.platform === 'empty') {
            if (badge) badge.classList.add('hidden');
            if (errorBox) errorBox.classList.add('hidden');
            if (previewContainer) previewContainer.classList.add('hidden');
            if (iframe) iframe.src = '';
            return;
        }

        if (!res.isValid) {
            if (badge) badge.classList.add('hidden');
            if (errorBox) {
                errorBox.innerText = '⚠️ Please enter a valid YouTube, Instagram, or Facebook video link';
                errorBox.classList.remove('hidden');
            }
            if (previewContainer) previewContainer.classList.add('hidden');
            if (iframe) iframe.src = '';
            return;
        }

        // Valid Link detected
        if (errorBox) errorBox.classList.add('hidden');
        if (badge) {
            badge.innerText = '✓ ' + res.badge;
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded shadow-2xs ' + res.badgeClass;
            badge.classList.remove('hidden');
        }
        if (previewContainer && iframe) {
            previewContainer.classList.remove('hidden');
            if (iframe.src !== res.embedUrl) {
                iframe.src = res.embedUrl;
            }
        }
    }

    function toggleVideoInputs(val, mode) {
        const linkGroup = document.getElementById(mode + '-link-input-group');
        const uploadGroup = document.getElementById(mode + '-upload-input-group');
        if (!linkGroup || !uploadGroup) return;

        if (val === 'upload') {
            linkGroup.classList.add('hidden');
            uploadGroup.classList.remove('hidden');
        } else {
            linkGroup.classList.remove('hidden');
            uploadGroup.classList.add('hidden');
            previewVideoLink(mode);
        }
    }

    function validateVideoForm(form, mode) {
        const typeSelect = form.querySelector('[name="video_type"]');
        if (!typeSelect) return true;

        if (typeSelect.value !== 'upload') {
            const urlInput = form.querySelector('[name="video_url"]');
            const res = parseVideoUrl(urlInput ? urlInput.value : '');
            if (!res.isValid) {
                alert('Please enter a valid YouTube, Instagram, or Facebook video link.');
                if (urlInput) urlInput.focus();
                return false;
            }
        }
        return true;
    }

    function openAddVideoModal() {
        document.getElementById('add-video-type').value = 'link';
        toggleVideoInputs('link', 'add');
        document.body.classList.add('modal-open');
        document.getElementById('add-video-modal').classList.remove('hidden');
    }

    function closeAddVideoModal() {
        document.getElementById('add-video-modal').classList.add('hidden');
        document.body.classList.remove('modal-open');
        const iframe = document.getElementById('add-preview-iframe');
        if (iframe) iframe.src = '';
    }

    function openEditVideoModal(v) {
        const form = document.getElementById('edit-video-form');
        form.action = '<?= url('admin/videos/update/') ?>' + v.id;

        document.getElementById('edit-video-title').value = v.title || '';
        
        const isUpload = (v.video_type === 'upload');
        const selectVal = isUpload ? 'upload' : 'link';
        document.getElementById('edit-video-type').value = selectVal;
        document.getElementById('edit-video-url').value = v.video_url || '';
        document.getElementById('edit-product-url').value = v.product_url || '';
        document.getElementById('edit-video-description').value = v.description || '';
        document.getElementById('edit-video-order').value = v.display_order || 1;

        toggleVideoInputs(selectVal, 'edit');
        if (!isUpload) {
            previewVideoLink('edit');
        }

        document.body.classList.add('modal-open');
        document.getElementById('edit-video-modal').classList.remove('hidden');
    }

    function closeEditVideoModal() {
        document.getElementById('edit-video-modal').classList.add('hidden');
        document.body.classList.remove('modal-open');
        const iframe = document.getElementById('edit-preview-iframe');
        if (iframe) iframe.src = '';
    }
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>