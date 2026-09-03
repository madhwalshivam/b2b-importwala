<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php
$shareUrl = url('blog/' . $post['slug']);
$shareTitle = urlencode($post['title']);
$whatsappShareUrl = "https://api.whatsapp.com/send?text=" . urlencode($post['title'] . " - " . $shareUrl);
?>

<!-- Custom Article Content Styling for Light Theme -->
<style>
    .article-body h1 {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0f172a;
        margin-top: 1.75rem;
        margin-bottom: 0.75rem;
        line-height: 1.3;
    }
    .article-body h2 {
        font-size: 1.375rem;
        font-weight: 700;
        color: #1e293b;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        line-height: 1.35;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 0.375rem;
    }
    .article-body h3 {
        font-size: 1.125rem;
        font-weight: 600;
        color: #334155;
        margin-top: 1.25rem;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    .article-body p {
        margin-bottom: 1.25rem;
        line-height: 1.75;
        color: #334155;
        font-size: 0.95rem;
    }
    .article-body ul {
        list-style-type: disc;
        padding-left: 1.5rem;
        margin-bottom: 1.25rem;
        color: #334155;
    }
    .article-body ol {
        list-style-type: decimal;
        padding-left: 1.5rem;
        margin-bottom: 1.25rem;
        color: #334155;
    }
    .article-body li {
        margin-bottom: 0.375rem;
        font-size: 0.95rem;
        line-height: 1.6;
    }
    .article-body a {
        color: #f05a29;
        font-weight: 600;
        text-decoration: underline;
        text-underline-offset: 3px;
        transition: color 0.15s;
    }
    .article-body a:hover {
        color: #c2410c;
    }
    .article-body blockquote {
        border-left: 4px solid #f05a29;
        padding: 0.875rem 1.25rem;
        font-style: italic;
        color: #1e293b;
        margin: 1.5rem 0;
        background-color: #f8fafc;
        border-radius: 0 0.75rem 0.75rem 0;
    }
    .article-body img {
        max-width: 100%;
        height: auto;
        border-radius: 0.75rem;
        margin: 1.5rem auto;
        display: block;
        border: 1px solid #e2e8f0;
    }
    .article-body table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
        font-size: 0.85rem;
        background-color: #ffffff;
        border-radius: 0.75rem;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .article-body th {
        background-color: #f8fafc;
        font-weight: 700;
        color: #0f172a;
        text-align: left;
        padding: 0.75rem 1rem;
        border-bottom: 2px solid #cbd5e1;
    }
    .article-body td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e2e8f0;
        color: #334155;
    }
</style>

<!-- BREADCRUMB HEADER -->
<div class="bg-slate-50 border-b border-slate-200 py-3.5 font-sans">
    <div class="container mx-auto px-4">
        <nav class="flex items-center space-x-2 text-xs font-normal text-slate-500">
            <a href="<?= url('/') ?>" class="hover:text-[#f05a29] transition">Home</a>
            <span>/</span>
            <a href="<?= url('blog') ?>" class="hover:text-[#f05a29] transition">Blog</a>
            <span>/</span>
            <span class="text-slate-900 font-semibold truncate max-w-xs md:max-w-md">
                <?= htmlspecialchars(htmlspecialchars_decode($post['title'], ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?>
            </span>
        </nav>
    </div>
</div>

<!-- SINGLE ARTICLE CONTAINER -->
<main class="py-10 bg-white font-sans min-h-[70vh] text-slate-900">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- MAIN ARTICLE CONTENT (8 Columns) -->
            <article class="lg:col-span-8 space-y-6">

                <!-- Article Header & Metadata -->
                <div class="space-y-3">
                    <?php if (!empty($post['category_name'])): ?>
                        <span class="inline-block px-3 py-1 bg-[#f05a29]/10 text-[#f05a29] font-bold text-[11px] uppercase rounded-lg tracking-wider border border-[#f05a29]/20">
                            <?= htmlspecialchars($post['category_name']) ?>
                        </span>
                    <?php endif; ?>

                    <!-- Main Display Title -->
                    <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-snug">
                        <?= htmlspecialchars(htmlspecialchars_decode($post['title'], ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?>
                    </h1>

                    <!-- Author, Date, Views, Share Bar -->
                    <div class="pt-3 border-t border-b border-slate-200 py-3 flex flex-wrap items-center justify-between gap-4 text-xs text-slate-500 font-medium">
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-7 h-7 rounded-full bg-[#f05a29] text-white flex items-center justify-center font-bold text-xs">
                                    <?= strtoupper(substr($post['author_name'] ?: 'I', 0, 1)) ?>
                                </div>
                                <span class="text-slate-900 font-bold"><?= htmlspecialchars($post['author_name'] ?: 'ImportWale Team') ?></span>
                            </div>
                            <span>•</span>
                            <span><?= date('F d, Y', strtotime($post['published_at'] ?: $post['created_at'])) ?></span>
                        </div>

                        <!-- Share Buttons -->
                        <div class="flex items-center space-x-2">
                            <span class="text-slate-500 text-xs font-semibold mr-1">Share:</span>
                            <a href="<?= $whatsappShareUrl ?>" target="_blank" rel="noopener"
                                class="inline-flex items-center space-x-1 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white rounded-xl text-xs font-bold transition border border-emerald-200">
                                <i data-lucide="share-2" class="w-3.5 h-3.5"></i>
                                <span>WhatsApp</span>
                            </a>
                            <button type="button" onclick="copyArticleLink('<?= $shareUrl ?>')"
                                class="inline-flex items-center space-x-1 px-3 py-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-xl text-xs font-bold transition cursor-pointer border border-slate-200">
                                <i data-lucide="link" class="w-3.5 h-3.5"></i>
                                <span id="copy-btn-text">Copy Link</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- FEATURED IMAGE WITH ALT TAG & ONERROR FALLBACK -->
                <?php if (!empty($post['featured_image'])): ?>
                    <div class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 aspect-[16/9] relative">
                        <img src="<?= asset($post['featured_image']) ?>"
                            alt="<?= htmlspecialchars($post['featured_image_alt'] ?: $post['title']) ?>"
                            class="w-full h-full object-cover"
                            onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                        <div class="hidden w-full h-full flex items-center justify-center text-slate-400 bg-slate-100">
                            <i data-lucide="newspaper" class="w-12 h-12 opacity-40"></i>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- EXCERPT / SUMMARY -->
                <?php if (!empty($post['excerpt'])): ?>
                    <div class="bg-slate-50 p-4 rounded-xl border-l-4 border-[#f05a29] text-slate-800 text-xs md:text-sm font-medium leading-relaxed italic border border-slate-200">
                        <?= htmlspecialchars($post['excerpt']) ?>
                    </div>
                <?php endif; ?>

                <!-- ARTICLE BODY CONTENT (WYSIWYG HTML) -->
                <div class="article-body">
                    <?= $post['content'] ?>
                </div>

                <!-- AUTHOR FOOTER BOX -->
                <div class="pt-6 border-t border-slate-200">
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 flex items-center space-x-4">
                        <div class="w-11 h-11 rounded-2xl bg-[#f05a29] text-white flex items-center justify-center font-bold text-lg shrink-0">
                            <?= strtoupper(substr($post['author_name'] ?: 'I', 0, 1)) ?>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Written by <?= htmlspecialchars($post['author_name'] ?: 'ImportWale Team') ?></h4>
                            <p class="text-[11px] text-slate-500 font-normal mt-0.5">
                                ImportWale B2B wholesale experts providing verified insights and market guides.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- RELATED ARTICLES SECTION (3 cards) -->
                <?php if (!empty($relatedPosts)): ?>
                    <div class="pt-8 border-t border-slate-200 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-slate-900 tracking-tight">Related Articles</h3>
                            <a href="<?= url('blog') ?>" class="text-xs text-[#f05a29] font-bold hover:underline">View All &rarr;</a>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <?php foreach ($relatedPosts as $rel): ?>
                                <a href="<?= url('blog/' . $rel['slug']) ?>"
                                    class="group block bg-white hover:bg-white p-3.5 rounded-2xl border border-slate-200 hover:border-[#f05a29] transition flex flex-col justify-between shadow-xs hover:shadow-md">
                                    <div class="space-y-2">
                                        <div class="aspect-[16/9] rounded-xl overflow-hidden bg-slate-100 relative border border-slate-100">
                                            <?php if (!empty($rel['featured_image'])): ?>
                                                <img src="<?= asset($rel['featured_image']) ?>"
                                                    alt="<?= htmlspecialchars($rel['featured_image_alt'] ?: $rel['title']) ?>"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                    onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                                <div class="hidden w-full h-full flex items-center justify-center text-slate-400 bg-slate-100">
                                                    <i data-lucide="newspaper" class="w-6 h-6 opacity-40"></i>
                                                </div>
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-slate-400 bg-slate-100">
                                                    <i data-lucide="newspaper" class="w-6 h-6 opacity-40"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <h4 class="text-xs font-bold text-slate-900 group-hover:text-[#f05a29] transition line-clamp-2 leading-snug">
                                            <?= htmlspecialchars($rel['title']) ?>
                                        </h4>
                                        <p class="text-[11px] text-slate-600 font-normal line-clamp-2 leading-relaxed">
                                            <?= htmlspecialchars($rel['excerpt'] ?: mb_strimwidth(strip_tags($rel['content']), 0, 80, '...')) ?>
                                        </p>
                                    </div>
                                    <div class="pt-2 mt-3 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500 font-medium">
                                        <span><?= date('M d, Y', strtotime($rel['published_at'] ?: $rel['created_at'])) ?></span>
                                        <span class="text-[#f05a29] font-bold">Read &rarr;</span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </article>

            <!-- RIGHT SIDEBAR (4 Columns) -->
            <aside class="lg:col-span-4 space-y-6">

                <!-- RECENT ARTICLES WIDGET -->
                <?php if (!empty($recentPosts)): ?>
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 space-y-4 shadow-xs">
                        <div class="flex items-center space-x-2 border-b border-slate-100 pb-3">
                            <i data-lucide="newspaper" class="w-4 h-4 text-[#f05a29]"></i>
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Recent Articles</h3>
                        </div>

                        <div class="space-y-3.5">
                            <?php foreach ($recentPosts as $rp): ?>
                                <a href="<?= url('blog/' . $rp['slug']) ?>" class="flex items-start space-x-3 group">
                                    <div class="w-16 h-16 rounded-xl bg-slate-100 overflow-hidden shrink-0 border border-slate-200 relative">
                                        <?php if (!empty($rp['featured_image'])): ?>
                                            <img src="<?= asset($rp['featured_image']) ?>"
                                                alt="<?= htmlspecialchars($rp['featured_image_alt'] ?: $rp['title']) ?>"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                            <div class="hidden w-full h-full flex items-center justify-center text-slate-400 bg-slate-100">
                                                <i data-lucide="newspaper" class="w-5 h-5 opacity-40"></i>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center text-slate-400 bg-slate-100">
                                                <i data-lucide="newspaper" class="w-5 h-5 opacity-40"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="space-y-0.5">
                                        <span class="text-[10px] text-slate-400 font-medium">
                                            <?= date('M d, Y', strtotime($rp['published_at'] ?: $rp['created_at'])) ?>
                                        </span>
                                        <h4 class="text-xs font-bold text-slate-900 group-hover:text-[#f05a29] transition line-clamp-2 leading-snug">
                                            <?= htmlspecialchars($rp['title']) ?>
                                        </h4>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <div class="pt-2 border-t border-slate-100">
                            <a href="<?= url('blog') ?>" class="block text-center py-1.5 text-xs font-bold text-[#f05a29] hover:underline">
                                View All Articles &rarr;
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- B2B WHOLESALE PROMO WIDGET -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-6 rounded-2xl text-white space-y-4 shadow-md">
                    <div class="w-9 h-9 rounded-xl bg-[#f05a29]/20 border border-[#f05a29]/30 flex items-center justify-center text-[#f05a29]">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold">ImportWale B2B Wholesale</h4>
                        <p class="text-xs text-slate-300 font-normal mt-1 leading-relaxed">
                            Source direct factory wholesale items at bulk discount rates with express nationwide shipping.
                        </p>
                    </div>
                    <a href="<?= url('shop') ?>" class="inline-block w-full text-center py-2.5 bg-[#f05a29] hover:bg-orange-600 text-white font-bold text-xs rounded-xl transition shadow-xs">
                        Browse Wholesale Catalog
                    </a>
                </div>

            </aside>

        </div>
    </div>
</main>

<script>
    function copyArticleLink(url) {
        navigator.clipboard.writeText(url).then(() => {
            const btnText = document.getElementById('copy-btn-text');
            if (btnText) {
                btnText.textContent = 'Copied!';
                setTimeout(() => { btnText.textContent = 'Copy Link'; }, 2000);
            }
        }).catch(err => {
            alert('URL: ' + url);
        });
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>