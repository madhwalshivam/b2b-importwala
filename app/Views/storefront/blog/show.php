<?php include __DIR__ . '/../layouts/header.php'; ?>

<!-- Custom Article Content Styling for Typography, Tables, Headings & Links -->
<style>
    .article-body h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #111827;
        margin-top: 1.75rem;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }

    .article-body h2 {
        font-size: 1.375rem;
        font-weight: 700;
        color: #1f2937;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        line-height: 1.35;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 0.375rem;
    }

    .article-body h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #374151;
        margin-top: 1.25rem;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    .article-body p {
        margin-bottom: 1.25rem;
        line-height: 1.7;
        color: #4b5563;
        font-size: 0.9rem;
    }

    .article-body ul {
        list-style-type: disc;
        padding-left: 1.5rem;
        margin-bottom: 1.25rem;
        color: #4b5563;
    }

    .article-body ol {
        list-style-type: decimal;
        padding-left: 1.5rem;
        margin-bottom: 1.25rem;
        color: #4b5563;
    }

    .article-body li {
        margin-bottom: 0.375rem;
        font-size: 0.9rem;
        line-height: 1.6;
    }

    .article-body a {
        color: #dc2626;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 3px;
        transition: color 0.15s;
    }

    .article-body a:hover {
        color: #b91c1c;
    }

    .article-body blockquote {
        border-left: 4px solid #dc2626;
        padding-left: 1rem;
        font-style: italic;
        color: #4b5563;
        margin: 1.25rem 0;
        background-color: #f9fafb;
        padding: 0.875rem 1rem;
        border-radius: 0 0.75rem 0.75rem 0;
    }

    .article-body img {
        max-width: 100%;
        height: auto;
        border-radius: 0.75rem;
        margin: 1.25rem auto;
        display: block;
        border: 1px solid #e5e7eb;
    }

    .article-body table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.25rem 0;
        font-size: 0.85rem;
        background-color: #ffffff;
        border-radius: 0.75rem;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .article-body th {
        background-color: #f9fafb;
        font-weight: 600;
        color: #111827;
        text-align: left;
        padding: 0.625rem 0.875rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .article-body td {
        padding: 0.625rem 0.875rem;
        border-bottom: 1px solid #f3f4f6;
        color: #4b5563;
    }

    .article-body tr:hover {
        background-color: #f9fafb;
    }

    /* Dark Mode Crisp White Text & Typography Overrides */
    html.dark .article-body h1,
    html.dark .article-body h2,
    html.dark .article-body h3,
    html.dark .article-body h4,
    html.dark .article-body h5,
    html.dark .article-body h6 {
        color: #ffffff !important;
        border-bottom-color: #374151 !important;
    }

    html.dark .article-body p,
    html.dark .article-body ul,
    html.dark .article-body ol,
    html.dark .article-body li,
    html.dark .article-body span,
    html.dark .article-body div {
        color: #f3f4f6 !important;
    }

    html.dark .article-body blockquote {
        background-color: #111827 !important;
        color: #f3f4f6 !important;
        border-left-color: #ef4444 !important;
    }

    html.dark .article-body table {
        background-color: #111827 !important;
        border-color: #374151 !important;
    }

    html.dark .article-body th {
        background-color: #1f2937 !important;
        color: #ffffff !important;
        border-bottom-color: #374151 !important;
    }

    html.dark .article-body td {
        border-bottom-color: #374151 !important;
        color: #e5e7eb !important;
    }

    html.dark .article-body tr:hover {
        background-color: #1f2937 !important;
    }
</style>

<!-- BREADCRUMB HEADER -->
<div class="bg-theme-bg border-b border-gray-900 py-4 font-sans">
    <div class="container mx-auto px-4">
        <nav class="flex items-center space-x-2 text-xs font-normal text-gray-500">
            <a href="<?= url('/') ?>" class="hover:text-red-600 transition">Home</a>
            <span>/</span>
            <a href="<?= url('blog') ?>" class="hover:text-red-600 transition">Blog</a>
            <span>/</span>
            <span
                class="text-gray-900 font-semibold truncate max-w-xs md:max-w-md"><?= htmlspecialchars(htmlspecialchars_decode($post['title'], ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?></span>
        </nav>
    </div>
</div>

<!-- SINGLE ARTICLE CONTAINER -->
<main class="py-10 bg-theme-bg font-sans min-h-[70vh]">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- MAIN ARTICLE CONTENT (8 Columns) -->
            <article class="lg:col-span-8 space-y-6">

                <!-- Article Header & Metadata -->
                <div class="space-y-3">
                    <span
                        class="inline-block px-3 py-1 bg-red-50 text-red-700 font-semibold text-[10px] uppercase rounded-full tracking-wider border border-red-100">
                        Electric Scooter Article
                    </span>

                    <!-- Main H1 Display Title -->
                    <h1 class="text-2xl md:text-3xl font-semibold text-gray-900 tracking-tight leading-snug">
                        <?= htmlspecialchars(htmlspecialchars_decode($post['title'], ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?>
                    </h1>

                    <!-- Author, Date, Views, Share Bar -->
                    <div
                        class="pt-3 border-t border-b border-gray-100 py-2.5 flex flex-wrap items-center justify-between gap-4 text-xs text-gray-500 font-normal">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2">
                                <div
                                    class="w-6 h-6 rounded-full bg-red-600 text-white flex items-center justify-center font-semibold text-[11px]">
                                    <?= strtoupper(substr($post['author_name'] ?: 'M', 0, 1)) ?>
                                </div>
                                <span
                                    class="text-gray-800 font-semibold"><?= htmlspecialchars($post['author_name'] ?: 'Mudsor Team') ?></span>
                            </div>
                            <span>•</span>
                            <span><?= date('F d, Y', strtotime($post['published_at'] ?: $post['created_at'])) ?></span>
                            <span>•</span>
                            <span class="font-mono text-[11px]"><?= number_format((int) ($post['views'] ?? 0)) ?>
                                views</span>
                        </div>
                    </div>
                </div>

                <!-- FEATURED IMAGE WITH MANDATORY ALT ATTRIBUTE -->
                <?php if (!empty($post['featured_image'])): ?>
                    <div class="rounded-2xl overflow-hidden border border-gray-900 bg-gray-50 aspect-video">
                        <img src="<?= asset($post['featured_image']) ?>"
                            alt="<?= htmlspecialchars($post['featured_image_alt'] ?: $post['title']) ?>"
                            class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>

                <!-- EXCERPT / HIGHLIGHT -->
                <?php if (!empty($post['excerpt'])): ?>
                    <div
                        class="bg-gray-50 p-4 rounded-xl border-l-4 border-red-600 text-gray-700 text-xs md:text-sm font-medium leading-relaxed italic">
                        <?= htmlspecialchars($post['excerpt']) ?>
                    </div>
                <?php endif; ?>

                <!-- ARTICLE BODY CONTENT (WYSIWYG HTML) -->
                <div class="article-body">
                    <?= $post['content'] ?>
                </div>

                <!-- AUTHOR BIO / FOOTER BOX -->
                <div class="pt-6 border-t border-gray-900">
                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900 flex items-center space-x-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center font-semibold text-base shrink-0">
                            <?= strtoupper(substr($post['author_name'] ?: 'M', 0, 1)) ?>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-gray-900">Written by
                                <?= htmlspecialchars($post['author_name'] ?: 'Mudsor Team') ?>
                            </h4>
                            <p class="text-[11px] text-gray-500 font-normal mt-0.5">Electric scooter accessory experts
                                dedicated to giving EV owners reliable guides, fitting advice, and product comparisons.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- RELATED BLOGS SECTION -->
                <?php if (!empty($relatedPosts)): ?>
                    <div class="pt-8 border-t border-gray-900 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-900 tracking-tight">Related Articles</h3>
                            <a href="<?= url('blog') ?>" class="text-xs text-red-600 font-semibold hover:underline">View All
                                &rarr;</a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <?php foreach ($relatedPosts as $rel): ?>
                                <div
                                    class="bg-gray-50/70 p-3.5 rounded-2xl border border-gray-900/80 hover:bg-white hover:shadow-xs transition group flex flex-col justify-between">
                                    <div>
                                        <a href="<?= url('blog/' . $rel['slug']) ?>"
                                            class="block aspect-[16/9] rounded-xl overflow-hidden bg-gray-100 mb-2.5 relative">
                                            <?php if (!empty($rel['featured_image'])): ?>
                                                <img src="<?= asset($rel['featured_image']) ?>"
                                                    alt="<?= htmlspecialchars($rel['featured_image_alt'] ?: $rel['title']) ?>"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                    <i data-lucide="image" class="w-6 h-6"></i>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                        <h4
                                            class="text-xs font-semibold text-gray-900 group-hover:text-red-600 transition line-clamp-2 leading-snug mb-1.5">
                                            <a href="<?= url('blog/' . $rel['slug']) ?>">
                                                <?= htmlspecialchars($rel['title']) ?>
                                            </a>
                                        </h4>
                                        <p class="text-[11px] text-gray-500 font-normal line-clamp-2 leading-relaxed">
                                            <?= htmlspecialchars($rel['excerpt'] ?: mb_strimwidth(strip_tags($rel['content']), 0, 80, '...')) ?>
                                        </p>
                                    </div>
                                    <div
                                        class="pt-2 mt-3 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-400">
                                        <span><?= date('M d, Y', strtotime($rel['published_at'] ?: $rel['created_at'])) ?></span>
                                        <span class="text-red-600 font-semibold group-hover:underline">Read &rarr;</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </article>

            <!-- RIGHT SIDEBAR (4 Columns) -->
            <aside class="lg:col-span-4 space-y-6">

                <!-- RECENT ARTICLES WIDGET -->
                <?php if (!empty($recentPosts)): ?>
                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-900 space-y-4">
                        <div class="flex items-center space-x-2 border-b border-gray-900 pb-3">
                            <i data-lucide="newspaper" class="w-4 h-4 text-red-600"></i>
                            <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wider">Recent Articles</h3>
                        </div>

                        <div class="space-y-3.5">
                            <?php foreach ($recentPosts as $rp): ?>
                                <div class="flex items-start space-x-3 group">
                                    <a href="<?= url('blog/' . $rp['slug']) ?>"
                                        class="w-16 h-16 rounded-xl bg-gray-900 overflow-hidden shrink-0 border border-gray-900">
                                        <?php if (!empty($rp['featured_image'])): ?>
                                            <img src="<?= asset($rp['featured_image']) ?>"
                                                alt="<?= htmlspecialchars($rp['featured_image_alt'] ?: $rp['title']) ?>"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                <i data-lucide="image" class="w-5 h-5"></i>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                    <div class="space-y-0.5">
                                        <span class="text-[10px] text-gray-400 font-normal">
                                            <?= date('M d, Y', strtotime($rp['published_at'] ?: $rp['created_at'])) ?>
                                        </span>
                                        <h4
                                            class="text-xs font-semibold text-gray-900 group-hover:text-red-600 transition line-clamp-2 leading-snug">
                                            <a href="<?= url('blog/' . $rp['slug']) ?>">
                                                <?= htmlspecialchars($rp['title']) ?>
                                            </a>
                                        </h4>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="pt-2 border-t border-gray-900">
                            <a href="<?= url('blog') ?>"
                                class="block text-center py-1.5 text-xs font-semibold text-red-600 hover:text-red-700 transition">
                                View All Articles &rarr;
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- STORE PROMO WIDGET -->
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 p-6 rounded-2xl text-white space-y-4 shadow-md">
                    <div
                        class="w-9 h-9 rounded-xl bg-red-600/20 border border-red-500/30 flex items-center justify-center text-red-400">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold">Premium EV Accessories</h4>
                        <p class="text-xs text-gray-300 font-normal mt-1 leading-relaxed">
                            Protect your electric scooter with heavy-duty stainless steel guards, waterproof covers, and
                            mobile holders designed specifically for your model.
                        </p>
                    </div>
                    <a href="<?= url('shop') ?>"
                        class="inline-block w-full text-center py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition">
                        Shop Accessories Now
                    </a>
                </div>

            </aside>

        </div>
    </div>
</main>

<?php include __DIR__ . '/../layouts/footer.php'; ?>