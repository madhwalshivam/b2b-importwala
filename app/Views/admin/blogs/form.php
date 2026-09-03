<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php
$isEdit = !empty($post);
$formAction = $isEdit ? url('admin/blogs/update/' . $post['id']) : url('admin/blogs/store');
?>

<!-- Include TinyMCE Rich Text Editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="no-referrer"></script>

<div class="space-y-6">

    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <a href="<?= url('admin/blogs') ?>" class="text-xs text-slate-500 hover:text-red-600 font-bold transition flex items-center space-x-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Back to Articles</span>
                </a>
                <span class="text-slate-400 text-xs">•</span>
                <span class="px-2.5 py-0.5 text-[11px] font-bold uppercase bg-red-50 text-red-600 rounded-md border border-red-100">
                    <?= $isEdit ? 'Edit Article' : 'New Article' ?>
                </span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1 tracking-tight"><?= htmlspecialchars($title) ?></h1>
        </div>
        <div class="flex items-center space-x-3">
            <a href="<?= url('admin/blogs') ?>" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                Cancel
            </a>
            <button type="submit" form="blog-form" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span><?= $isEdit ? 'Save Changes' : 'Save &amp; Publish' ?></span>
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Main Form Grid -->
    <form id="blog-form" action="<?= $formAction ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left Main Column (Title, Slug, Category, Body Content, SEO) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Title & Clean URL Slug Box -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                    <div>
                        <label for="blog_title" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                            Article Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" id="blog_title"
                            value="<?= htmlspecialchars($post['title'] ?? '') ?>" required
                            placeholder="e.g. B2B Jewellery Sourcing Guide for Festival Season"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:bg-white focus:outline-none focus:border-red-600 transition">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Clean URL Slug Field -->
                        <div>
                            <label for="blog_slug" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                                Clean URL Slug <span class="text-red-500">*</span>
                            </label>
                            <div class="relative flex items-center">
                                <span class="px-2.5 py-2 bg-slate-100 border border-r-0 border-slate-200 rounded-l-xl text-xs text-slate-500 font-mono select-none">
                                    /blog/
                                </span>
                                <input type="text" name="slug" id="blog_slug"
                                    value="<?= htmlspecialchars($post['slug'] ?? '') ?>"
                                    placeholder="b2b-jewellery-sourcing-guide"
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-r-xl text-xs font-mono text-slate-800 focus:bg-white focus:outline-none focus:border-red-600 transition">
                            </div>
                        </div>

                        <!-- Category Selector -->
                        <div>
                            <label for="category_id" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                                Article Category
                            </label>
                            <select name="category_id" id="category_id" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:border-red-600 transition">
                                <option value="">-- Select Category --</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ((int)($post['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Rich Text WYSIWYG Editor Body -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Article Content</h2>
                            <p class="text-xs text-slate-500 font-normal">Use headings (H1, H2, H3), bullet points, tables, blockquotes, and embedded images.</p>
                        </div>
                    </div>
                    <div>
                        <textarea name="content" id="blog_content" rows="18" class="w-full"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Excerpt Field -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="excerpt" class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Short Excerpt / Card Summary
                        </label>
                        <span class="text-[11px] text-slate-400">Auto-generated from content if left blank</span>
                    </div>
                    <textarea name="excerpt" id="excerpt" rows="3"
                        placeholder="Brief 2-line summary shown on homepage and journal cards..."
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:border-red-600 transition"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
                </div>

                <!-- SEO Meta Fields Section -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                    <div class="flex items-center space-x-2 border-b border-slate-100 pb-3">
                        <i data-lucide="search" class="w-4 h-4 text-red-600"></i>
                        <h2 class="text-sm font-bold text-slate-900">SEO &amp; Open Graph Meta Fields</h2>
                    </div>

                    <!-- Meta Title Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="meta_title" class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Meta Title
                            </label>
                            <span id="meta_title_counter" class="text-xs font-mono font-medium text-slate-400">0 / 60 chars</span>
                        </div>
                        <input type="text" name="meta_title" id="meta_title"
                            value="<?= htmlspecialchars($post['meta_title'] ?? '') ?>"
                            placeholder="Custom Title Tag for Google Search"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:border-red-600 transition">
                    </div>

                    <!-- Meta Description Field -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="meta_description" class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Meta Description
                            </label>
                            <span id="meta_desc_counter" class="text-xs font-mono font-medium text-slate-400">0 / 160 chars</span>
                        </div>
                        <textarea name="meta_description" id="meta_description" rows="3"
                            placeholder="Snippet description shown in Google search results"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:border-red-600 transition"><?= htmlspecialchars($post['meta_description'] ?? '') ?></textarea>
                    </div>

                    <!-- Focus Keyword -->
                    <div>
                        <label for="focus_keyword" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                            Focus Keyword
                        </label>
                        <input type="text" name="focus_keyword" id="focus_keyword"
                            value="<?= htmlspecialchars($post['focus_keyword'] ?? '') ?>"
                            placeholder="e.g. b2b wholesale jewellery"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:border-red-600 transition">
                    </div>
                </div>

            </div>

            <!-- Right Sidebar (Publish Settings, Date, Featured Image) -->
            <div class="space-y-6">

                <!-- Publishing Box -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                    <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center space-x-2">
                        <i data-lucide="send" class="w-4 h-4 text-slate-500"></i>
                        <span>Publishing Settings</span>
                    </h2>

                    <!-- Status Dropdown -->
                    <div>
                        <label for="status" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:border-red-600 transition">
                            <option value="published" <?= ($post['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Published (Live on Website)</option>
                            <option value="draft" <?= ($post['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft (Saved Privately)</option>
                            <option value="scheduled" <?= ($post['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled (Future Date)</option>
                        </select>
                    </div>

                    <!-- Publish Date & Future Date Support -->
                    <div>
                        <label for="published_at" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                            Publication Date / Schedule
                        </label>
                        <input type="datetime-local" name="published_at" id="published_at"
                            value="<?= !empty($post['published_at']) ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : date('Y-m-d\TH:i') ?>"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:border-red-600 transition">
                        <p class="text-[10px] text-slate-400 mt-1 font-medium">Future date = auto-hidden until date passes.</p>
                    </div>

                    <!-- Author Name -->
                    <div>
                        <label for="author_name" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                            Author Name
                        </label>
                        <input type="text" name="author_name" id="author_name"
                            value="<?= htmlspecialchars($post['author_name'] ?? $defaultAuthor) ?>"
                            placeholder="ImportWale Team"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:border-red-600 transition">
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <button type="submit" class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span><?= $isEdit ? 'Update Article' : 'Save &amp; Publish Article' ?></span>
                        </button>
                    </div>
                </div>

                <!-- Featured Image Box -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                    <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center space-x-2">
                        <i data-lucide="image" class="w-4 h-4 text-red-600"></i>
                        <span>Featured Image</span>
                    </h2>

                    <!-- Image Preview Container -->
                    <div id="image_preview_box" class="relative rounded-xl border border-slate-200 bg-slate-50 overflow-hidden aspect-video flex items-center justify-center">
                        <?php if (!empty($post['featured_image'])): ?>
                            <img id="featured_image_preview" src="<?= asset($post['featured_image']) ?>" alt="Preview" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div id="featured_placeholder" class="text-center p-4 text-slate-400 space-y-1">
                                <i data-lucide="upload-cloud" class="w-8 h-8 mx-auto text-slate-300"></i>
                                <p class="text-xs font-semibold text-slate-500">Upload Featured Image</p>
                                <p class="text-[10px] text-slate-400">JPG, PNG, WEBP up to 5MB</p>
                            </div>
                            <img id="featured_image_preview" src="" alt="Preview" class="w-full h-full object-cover hidden">
                        <?php endif; ?>
                    </div>

                    <!-- Image File Input -->
                    <div>
                        <input type="file" name="featured_image" id="featured_image" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition cursor-pointer">
                    </div>

                    <!-- Image Alt Text -->
                    <div>
                        <label for="featured_image_alt" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                            Image Alt Tag
                        </label>
                        <input type="text" name="featured_image_alt" id="featured_image_alt"
                            value="<?= htmlspecialchars($post['featured_image_alt'] ?? '') ?>"
                            placeholder="Descriptive alt text for SEO"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:border-red-600 transition">
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<!-- JS Logic for Slug Auto-Generation, Character Counters, TinyMCE, and Image Preview -->
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const blogForm = document.getElementById('blog-form');
        if (blogForm) {
            blogForm.addEventListener('submit', function () {
                if (window.tinymce) {
                    tinymce.triggerSave();
                }
            });
        }

        // 1. Slug Auto-Generation
        const titleInput = document.getElementById('blog_title');
        const slugInput = document.getElementById('blog_slug');
        let slugManuallyEdited = <?= $isEdit && !empty($post['slug']) ? 'true' : 'false' ?>;

        function slugify(text) {
            return text
                .toString()
                .toLowerCase()
                .trim()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
        }

        if (titleInput && slugInput) {
            titleInput.addEventListener('input', function () {
                if (!slugManuallyEdited) {
                    slugInput.value = slugify(this.value);
                }
            });

            slugInput.addEventListener('input', function () {
                slugManuallyEdited = true;
                this.value = slugify(this.value);
            });
        }

        // 2. SEO Character Counters
        const metaTitleInput = document.getElementById('meta_title');
        const metaTitleCounter = document.getElementById('meta_title_counter');
        const metaDescInput = document.getElementById('meta_description');
        const metaDescCounter = document.getElementById('meta_desc_counter');

        if (metaTitleInput && metaTitleCounter) {
            metaTitleInput.addEventListener('input', function () {
                metaTitleCounter.textContent = this.value.length + ' / 60 chars';
            });
            metaTitleInput.dispatchEvent(new Event('input'));
        }

        if (metaDescInput && metaDescCounter) {
            metaDescInput.addEventListener('input', function () {
                metaDescCounter.textContent = this.value.length + ' / 160 chars';
            });
            metaDescInput.dispatchEvent(new Event('input'));
        }

        // 3. Featured Image Preview
        const featuredImgInput = document.getElementById('featured_image');
        const featuredImgPreview = document.getElementById('featured_image_preview');
        const featuredPlaceholder = document.getElementById('featured_placeholder');

        if (featuredImgInput && featuredImgPreview) {
            featuredImgInput.addEventListener('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        featuredImgPreview.src = e.target.result;
                        featuredImgPreview.classList.remove('hidden');
                        if (featuredPlaceholder) featuredPlaceholder.classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // 4. Initialize TinyMCE Rich Text Editor
        if (window.tinymce) {
            tinymce.init({
                selector: '#blog_content',
                height: 480,
                menubar: 'edit insert format table tools',
                plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code wordcount help',
                toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | code preview fullscreen',
                block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Blockquote=blockquote; Preformatted=pre',
                content_style: 'body { font-family: Inter, sans-serif; font-size:14px; line-height: 1.6; color: #334155; } h1 { font-size: 1.875rem; font-weight: 700; margin-top: 1rem; } h2 { font-size: 1.5rem; font-weight: 600; margin-top: 1rem; } h3 { font-size: 1.25rem; font-weight: 600; margin-top: 0.75rem; } img { max-width: 100%; height: auto; border-radius: 8px; } table { border-collapse: collapse; width: 100%; margin: 1rem 0; } table, th, td { border: 1px solid #cbd5e1; padding: 8px 12px; }',
                link_title: true,
                link_target_list: [
                    { text: 'Same window (_self)', value: '' },
                    { text: 'New window (_blank)', value: '_blank' }
                ],
                rel_list: [
                    { text: 'None', value: '' },
                    { text: 'No Follow (rel="nofollow")', value: 'nofollow' },
                    { text: 'Sponsored (rel="sponsored")', value: 'sponsored' }
                ],
                image_description: true,
                image_title: true,
                images_upload_url: '<?= url('admin/blogs/upload-image') ?>',
                automatic_uploads: true,
                images_upload_handler: function (blobInfo, progress) {
                    return new Promise((resolve, reject) => {
                        const xhr = new XMLHttpRequest();
                        xhr.withCredentials = false;
                        xhr.open('POST', '<?= url('admin/blogs/upload-image') ?>');

                        xhr.onload = function () {
                            if (xhr.status < 200 || xhr.status >= 300) {
                                reject('HTTP Error: ' + xhr.status);
                                return;
                            }
                            const json = JSON.parse(xhr.responseText);
                            if (!json || typeof json.location !== 'string') {
                                reject('Invalid JSON: ' + xhr.responseText);
                                return;
                            }
                            resolve(json.location);
                        };

                        xhr.onerror = function () {
                            reject('Image upload failed due to a XHR Transport error.');
                        };

                        const formData = new FormData();
                        formData.append('file', blobInfo.blob(), blobInfo.filename());
                        xhr.send(formData);
                    });
                }
            });
        }

    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>