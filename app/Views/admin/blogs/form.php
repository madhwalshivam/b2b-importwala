<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php
$isEdit = !empty($post);
$formAction = $isEdit ? url('admin/blogs/update/' . $post['id']) : url('admin/blogs/store');
?>

<!-- Include TinyMCE Rich Text Editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="no-referrer"></script>

<div class="space-y-6">

    <!-- Top Header -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <a href="<?= url('admin/blogs') ?>"
                    class="text-xs text-slate-500 hover:text-red-600 font-medium transition flex items-center space-x-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Back to Blogs</span>
                </a>
                <span class="text-slate-400 text-xs">•</span>
                <span
                    class="px-2.5 py-0.5 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-md border border-red-100">
                    <?= $isEdit ? 'Edit Post' : 'New Post' ?>
                </span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight"><?= htmlspecialchars($title) ?></h1>
        </div>
        <div class="flex items-center space-x-3">
            <a href="<?= url('admin/blogs') ?>"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                Cancel
            </a>
            <button type="submit" form="blog-form"
                class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center space-x-2">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span><?= $isEdit ? 'Save Changes' : 'Publish Blog' ?></span>
            </button>
        </div>
    </div>

    <!-- Flash Messages (Animated Toast Pop-up System) -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.MudsorToast) {
                    MudsorToast.show(<?= json_encode($_SESSION['flash_success']) ?>, 'success', 1800);
                }
            });
        </script>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.MudsorToast) {
                    MudsorToast.show(<?= json_encode($_SESSION['flash_error']) ?>, 'error', 2500);
                }
            });
        </script>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Main Form Grid -->
    <form id="blog-form" action="<?= $formAction ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left Main Column (Title, Slug, Body Content, SEO) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Title & Clean URL Slug Box -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                    <div>
                        <label for="blog_title"
                            class="block text-xs font-semibold text-slate-800 uppercase tracking-wider mb-1.5">
                            Blog Display Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" id="blog_title"
                            value="<?= htmlspecialchars($post['title'] ?? '') ?>" required
                            placeholder="e.g. ERW Pipe vs Seamless Pipe"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                    </div>

                    <!-- Clean URL Slug Field with Live Preview -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="blog_slug"
                                class="block text-xs font-semibold text-slate-800 uppercase tracking-wider">
                                Clean URL Slug <span class="text-red-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-400 font-medium">Auto-generated &amp; editable</span>
                        </div>
                        <div class="relative flex items-center">
                            <span
                                class="px-3 py-2 bg-slate-100 border border-r-0 border-slate-200 rounded-l-xl text-xs text-slate-500 font-mono select-none">
                                <?= url('blog/') ?>
                            </span>
                            <input type="text" name="slug" id="blog_slug"
                                value="<?= htmlspecialchars($post['slug'] ?? '') ?>"
                                placeholder="erw-pipe-vs-seamless-pipe"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-r-xl text-xs font-mono text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1.5 flex items-center space-x-1">
                            <i data-lucide="info" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                            <span>URL format: Clean, lowercase, hyphens only. Duplicate slugs will auto-append
                                numbers.</span>
                        </p>
                    </div>
                </div>

                <!-- Rich Text WYSIWYG Editor Body -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900">Article Body Content</h2>
                            <p class="text-xs text-slate-500">Format headings (H1, H2, H3), lists, tables,
                                internal/external links, and images with alt text.</p>
                        </div>
                    </div>
                    <div>
                        <textarea name="content" id="blog_content" rows="18"
                            class="w-full"><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Excerpt Field -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="excerpt"
                            class="block text-xs font-semibold text-slate-800 uppercase tracking-wider">
                            Short Excerpt / Summary
                        </label>
                        <span class="text-[11px] text-slate-400">Optional (Auto-generated from body if left
                            blank)</span>
                    </div>
                    <textarea name="excerpt" id="excerpt" rows="3"
                        placeholder="Brief summary of the article shown on blog listing cards..."
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
                </div>

                <!-- SEO Meta Fields Section -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                    <div class="flex items-center space-x-2 border-b border-slate-100 pb-3">
                        <i data-lucide="search" class="w-4 h-4 text-red-600"></i>
                        <h2 class="text-sm font-semibold text-slate-900">Search Engine Optimization (SEO Controls)</h2>
                    </div>

                    <!-- Meta Title Field + Character Counter -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="meta_title"
                                class="block text-xs font-semibold text-slate-800 uppercase tracking-wider">
                                Meta Title
                            </label>
                            <span id="meta_title_counter" class="text-xs font-mono font-medium text-slate-400">
                                0 / 60 characters
                            </span>
                        </div>
                        <input type="text" name="meta_title" id="meta_title"
                            value="<?= htmlspecialchars($post['meta_title'] ?? '') ?>"
                            placeholder="SEO Title for search engines (Recommended 50-60 characters)"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                        <p class="text-[11px] text-slate-400 mt-1">Leave empty to use default display title.</p>
                    </div>

                    <!-- Meta Description Field + Character Counter -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="meta_description"
                                class="block text-xs font-semibold text-slate-800 uppercase tracking-wider">
                                Meta Description
                            </label>
                            <span id="meta_desc_counter" class="text-xs font-mono font-medium text-slate-400">
                                0 / 160 characters
                            </span>
                        </div>
                        <textarea name="meta_description" id="meta_description" rows="3"
                            placeholder="Snippet summary shown in Google search results (Recommended 150-160 characters)"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition"><?= htmlspecialchars($post['meta_description'] ?? '') ?></textarea>
                    </div>

                    <!-- Focus Keyword Field -->
                    <div>
                        <label for="focus_keyword"
                            class="block text-xs font-semibold text-slate-800 uppercase tracking-wider mb-1.5">
                            Focus Keyword <span class="text-slate-400 font-normal lowercase">(optional reference)</span>
                        </label>
                        <input type="text" name="focus_keyword" id="focus_keyword"
                            value="<?= htmlspecialchars($post['focus_keyword'] ?? '') ?>"
                            placeholder="e.g. erw pipe vs seamless pipe"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                    </div>

                    <!-- Live Google Search Preview Box -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-1">
                        <p class="text-[11px] font-semibold uppercase text-slate-400 tracking-wider">Google Search
                            Result Preview</p>
                        <div class="text-blue-700 text-sm font-semibold hover:underline cursor-pointer truncate"
                            id="seo_preview_title">
                            <?= htmlspecialchars(($post['meta_title'] ?? '') ?: (($post['title'] ?? '') ?: 'Blog Title Preview')) ?>
                            | Mudsor
                        </div>
                        <div class="text-emerald-700 text-[11px] font-mono truncate" id="seo_preview_url">
                            <?= url('blog/') ?><span
                                id="seo_preview_slug_text"><?= htmlspecialchars(($post['slug'] ?? '') ?: 'your-blog-slug') ?></span>
                        </div>
                        <div class="text-slate-600 text-xs line-clamp-2" id="seo_preview_desc">
                            <?= htmlspecialchars(($post['meta_description'] ?? '') ?: (($post['excerpt'] ?? '') ?: 'This is how your blog post snippet will appear in search engine search results.')) ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Sidebar (Publish Settings & Featured Image) -->
            <div class="space-y-6">

                <!-- Publishing Box -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                    <h2
                        class="text-sm font-semibold text-slate-900 border-b border-slate-100 pb-3 flex items-center space-x-2">
                        <i data-lucide="send" class="w-4 h-4 text-slate-500"></i>
                        <span>Publishing Control</span>
                    </h2>

                    <!-- Status Dropdown -->
                    <div>
                        <label for="status"
                            class="block text-xs font-semibold text-slate-800 uppercase tracking-wider mb-1.5">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select name="status" id="status"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                            <option value="draft" <?= ($post['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft (Saved
                                Privately)</option>
                            <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>
                                Published (Live on Website)</option>
                        </select>
                    </div>

                    <!-- Author Name -->
                    <div>
                        <label for="author_name"
                            class="block text-xs font-semibold text-slate-800 uppercase tracking-wider mb-1.5">
                            Author Name
                        </label>
                        <input type="text" name="author_name" id="author_name"
                            value="<?= htmlspecialchars($post['author_name'] ?? $defaultAuthor) ?>"
                            placeholder="Mudsor Team"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                    </div>

                    <?php if ($isEdit): ?>
                        <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-400 space-y-1">
                            <div><strong class="text-slate-600">Created:</strong>
                                <?= date('M d, Y h:i A', strtotime($post['created_at'])) ?></div>
                            <?php if (!empty($post['published_at'])): ?>
                                <div><strong class="text-slate-600">Published:</strong>
                                    <?= date('M d, Y h:i A', strtotime($post['published_at'])) ?></div>
                            <?php endif; ?>
                            <div><strong class="text-slate-600">Views:</strong>
                                <?= number_format((int) ($post['views'] ?? 0)) ?></div>
                        </div>
                    <?php endif; ?>

                    <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                        <button type="submit"
                            class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center justify-center space-x-2">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span><?= $isEdit ? 'Update Blog Post' : 'Save &amp; Publish Post' ?></span>
                        </button>
                    </div>
                </div>

                <!-- Featured Image Box with Mandatory Alt Text -->
                <div
                    class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs space-y-4">
                    <h2
                        class="text-sm font-semibold text-slate-900 dark:text-slate-100 border-b border-slate-100 dark:border-slate-700 pb-3 flex items-center space-x-2">
                        <i data-lucide="image" class="w-4 h-4 text-red-600 dark:text-red-500"></i>
                        <span>Featured Image &amp; Alt Tag</span>
                    </h2>

                    <!-- Recommended Size Info Box -->
                    <div
                        class="p-3 bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-900/60 rounded-xl flex items-start space-x-2.5 text-xs text-amber-800 dark:text-amber-300">
                        <i data-lucide="ruler" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                        <div>
                            <p class="font-semibold text-[11px]">Recommended Size: 1200 × 675 px (16:9 Ratio)</p>
                            <p class="text-[10px] text-amber-700 dark:text-amber-400 font-normal mt-0.5 leading-tight">
                                Creating images at <strong>1200 × 675 px</strong> (or 800 × 450 px) ensures perfect fit
                                with zero overflow or cropping across Homepage, Blog Grid &amp; Related Articles.
                            </p>
                        </div>
                    </div>

                    <!-- Image Preview Container -->
                    <div id="image_preview_box"
                        class="relative rounded-xl border border-slate-200 bg-slate-50 overflow-hidden aspect-video flex items-center justify-center">
                        <?php if (!empty($post['featured_image'])): ?>
                            <img id="featured_image_preview" src="<?= asset($post['featured_image']) ?>" alt="Preview"
                                class="w-full h-full object-cover">
                        <?php else: ?>
                            <div id="featured_placeholder" class="text-center p-4 text-slate-400 space-y-1">
                                <i data-lucide="upload-cloud" class="w-8 h-8 mx-auto text-slate-300"></i>
                                <p class="text-xs font-medium text-slate-500">Upload Featured Image</p>
                                <p class="text-[10px] text-slate-400">JPG, PNG, WEBP up to 5MB</p>
                            </div>
                            <img id="featured_image_preview" src="" alt="Preview" class="w-full h-full object-cover hidden">
                        <?php endif; ?>
                    </div>

                    <!-- Image File Input -->
                    <div>
                        <input type="file" name="featured_image" id="featured_image" accept="image/*"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition cursor-pointer">
                    </div>

                    <!-- Featured Image Alt Text Input Field -->
                    <div>
                        <label for="featured_image_alt"
                            class="block text-xs font-semibold text-slate-800 uppercase tracking-wider mb-1.5">
                            Featured Image Alt Text <span class="text-slate-400 font-normal lowercase">(optional -
                                defaults to title)</span>
                        </label>
                        <input type="text" name="featured_image_alt" id="featured_image_alt"
                            value="<?= htmlspecialchars($post['featured_image_alt'] ?? '') ?>"
                            placeholder="Descriptive alt text for accessibility &amp; SEO"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                        <p class="text-[10px] text-slate-400 mt-1">Describes the image for search engines (auto-uses
                            title if left empty).</p>
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

        // 1. Slug Auto-Generation logic
        const titleInput = document.getElementById('blog_title');
        const slugInput = document.getElementById('blog_slug');
        const seoSlugPreview = document.getElementById('seo_preview_slug_text');
        let slugManuallyEdited = <?= $isEdit && !empty($post['slug']) ? 'true' : 'false' ?>;

        function slugify(text) {
            return text
                .toString()
                .toLowerCase()
                .trim()
                .replace(/\s+/g, '-')           // Replace spaces with -
                .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                .replace(/^-+/, '')             // Trim - from start of text
                .replace(/-+$/, '');            // Trim - from end of text
        }

        if (titleInput && slugInput) {
            titleInput.addEventListener('input', function () {
                if (!slugManuallyEdited) {
                    const generatedSlug = slugify(this.value);
                    slugInput.value = generatedSlug;
                    if (seoSlugPreview) seoSlugPreview.textContent = generatedSlug || 'your-blog-slug';
                }
                updateSeoTitlePreview();
            });

            slugInput.addEventListener('input', function () {
                slugManuallyEdited = true;
                this.value = slugify(this.value);
                if (seoSlugPreview) seoSlugPreview.textContent = this.value || 'your-blog-slug';
            });
        }

        // 2. SEO Character Counters & Live Preview
        const metaTitleInput = document.getElementById('meta_title');
        const metaTitleCounter = document.getElementById('meta_title_counter');
        const metaDescInput = document.getElementById('meta_description');
        const metaDescCounter = document.getElementById('meta_desc_counter');

        const seoPreviewTitle = document.getElementById('seo_preview_title');
        const seoPreviewDesc = document.getElementById('seo_preview_desc');

        function updateSeoTitlePreview() {
            const val = metaTitleInput.value.trim() || titleInput.value.trim() || 'Blog Title Preview';
            if (seoPreviewTitle) seoPreviewTitle.textContent = val + ' | Mudsor';
        }

        if (metaTitleInput && metaTitleCounter) {
            metaTitleInput.addEventListener('input', function () {
                const len = this.value.length;
                metaTitleCounter.textContent = len + ' / 60 characters';
                if (len >= 50 && len <= 60) {
                    metaTitleCounter.className = 'text-xs font-mono font-medium text-emerald-600';
                } else if (len > 60) {
                    metaTitleCounter.className = 'text-xs font-mono font-medium text-amber-600';
                } else {
                    metaTitleCounter.className = 'text-xs font-mono font-medium text-slate-400';
                }
                updateSeoTitlePreview();
            });
            // trigger initial count
            metaTitleInput.dispatchEvent(new Event('input'));
        }

        if (metaDescInput && metaDescCounter) {
            metaDescInput.addEventListener('input', function () {
                const len = this.value.length;
                metaDescCounter.textContent = len + ' / 160 characters';
                if (len >= 140 && len <= 160) {
                    metaDescCounter.className = 'text-xs font-mono font-medium text-emerald-600';
                } else if (len > 160) {
                    metaDescCounter.className = 'text-xs font-mono font-medium text-amber-600';
                } else {
                    metaDescCounter.className = 'text-xs font-mono font-medium text-slate-400';
                }
                if (seoPreviewDesc) {
                    seoPreviewDesc.textContent = this.value.trim() || 'This is how your blog post snippet will appear in search engine search results.';
                }
            });
            // trigger initial count
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

        // 4. Initialize TinyMCE Rich Text Editor with Link Options, Alt Text Prompts, Tables & Uploads
        if (window.tinymce) {
            tinymce.init({
                selector: '#blog_content',
                height: 480,
                menubar: 'edit insert format table tools',
                plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code wordcount help',
                toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | code preview fullscreen',
                block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Blockquote=blockquote; Preformatted=pre',
                content_style: 'body { font-family: Inter, sans-serif; font-size:14px; line-height: 1.6; color: #334155; } h1 { font-size: 1.875rem; font-weight: 700; margin-top: 1rem; } h2 { font-size: 1.5rem; font-weight: 600; margin-top: 1rem; } h3 { font-size: 1.25rem; font-weight: 600; margin-top: 0.75rem; } img { max-width: 100%; height: auto; border-radius: 8px; } table { border-collapse: collapse; width: 100%; margin: 1rem 0; } table, th, td { border: 1px solid #cbd5e1; padding: 8px 12px; }',
                // Enable Link Dialog with Target & Rel options (target=_blank, rel=nofollow)
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
                // Enable Alt Text prompt for images in editor body
                image_description: true,
                image_title: true,
                // Image upload endpoint handler
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