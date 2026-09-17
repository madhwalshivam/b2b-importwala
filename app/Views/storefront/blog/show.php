<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php
$shareUrl = url('blog/' . $post['slug']);
$shareTitle = urlencode($post['title']);
$whatsappShareUrl = "https://api.whatsapp.com/send?text=" . urlencode($post['title'] . " - " . $shareUrl);
?>

<!-- Custom Article Content Styling for Professional Theme -->
<style>
    .article-body {
        font-size: 1.125rem; /* 18px */
        line-height: 1.8;
        color: #334155;
    }
    .article-body h1 {
        font-size: 2.25rem;
        font-weight: 800;
        color: #0f172a;
        margin-top: 2.5rem;
        margin-bottom: 1.25rem;
        line-height: 1.2;
    }
    .article-body h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-top: 2.5rem;
        margin-bottom: 1rem;
        line-height: 1.3;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 0.5rem;
    }
    .article-body h3 {
        font-size: 1.375rem;
        font-weight: 600;
        color: #334155;
        margin-top: 2rem;
        margin-bottom: 0.75rem;
        line-height: 1.4;
    }
    .article-body p {
        margin-bottom: 1.5rem;
    }
    .article-body ul {
        list-style-type: disc;
        padding-left: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .article-body ol {
        list-style-type: decimal;
        padding-left: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .article-body li {
        margin-bottom: 0.5rem;
    }
    .article-body a {
        color: #f05a29;
        font-weight: 500;
        text-decoration: underline;
        text-underline-offset: 3px;
        transition: color 0.2s;
    }
    .article-body a:hover {
        color: #c2410c;
    }
    .article-body blockquote {
        border-left: 4px solid #f05a29;
        padding: 1.25rem 1.5rem;
        font-style: italic;
        color: #1e293b;
        margin: 2rem 0;
        background-color: #f8fafc;
        border-radius: 0 0.5rem 0.5rem 0;
        font-size: 1.25rem;
    }
    .article-body img {
        max-width: 100%;
        height: auto;
        border-radius: 0.75rem;
        margin: 2.5rem auto;
        display: block;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }
    .article-body table {
        width: 100%;
        border-collapse: collapse;
        margin: 2rem 0;
        font-size: 1rem;
        background-color: #ffffff;
        border-radius: 0.5rem;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
    }
    .article-body th {
        background-color: #f8fafc;
        font-weight: 700;
        color: #0f172a;
        text-align: left;
        padding: 1rem;
        border-bottom: 2px solid #e2e8f0;
    }
    .article-body td {
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }
</style>

<!-- BREADCRUMB HEADER -->
<div class="bg-slate-50 border-b border-slate-200 py-3.5 font-sans">
    <div class="max-w-4xl mx-auto px-4">
        <nav class="flex items-center space-x-2 text-sm font-medium text-slate-500">
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
<main class="py-12 md:py-16 bg-white font-sans text-slate-900">
    <article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Article Header & Metadata -->
        <header class="text-center space-y-6 mb-10">
            <?php if (!empty($post['category_name'])): ?>
                <span class="inline-block px-4 py-1.5 bg-[#f05a29]/10 text-[#f05a29] font-bold text-xs uppercase rounded-full tracking-widest">
                    <?= htmlspecialchars($post['category_name']) ?>
                </span>
            <?php endif; ?>

            <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                <?= htmlspecialchars(htmlspecialchars_decode($post['title'], ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <div class="flex items-center justify-center space-x-4 text-sm text-slate-500 font-medium pt-4">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        <?= strtoupper(substr($post['author_name'] ?: 'I', 0, 1)) ?>
                    </div>
                    <span class="text-slate-900 font-bold"><?= htmlspecialchars($post['author_name'] ?: 'ImportWale Team') ?></span>
                </div>
                <span>&bull;</span>
                <span><?= date('M d, Y', strtotime($post['published_at'] ?: $post['created_at'])) ?></span>
            </div>
        </header>

        <!-- FEATURED IMAGE -->
        <?php if (!empty($post['featured_image'])): ?>
            <figure class="mb-12">
                <div class="rounded-2xl overflow-hidden bg-slate-100 aspect-[16/9] md:aspect-[21/9] relative shadow-lg border border-slate-200">
                    <img src="<?= asset($post['featured_image']) ?>"
                        alt="<?= htmlspecialchars($post['featured_image_alt'] ?: $post['title']) ?>"
                        class="w-full h-full object-cover"
                        onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                    <div class="hidden w-full h-full flex items-center justify-center text-slate-400 bg-slate-100">
                        <i data-lucide="newspaper" class="w-12 h-12 opacity-40"></i>
                    </div>
                </div>
            </figure>
        <?php endif; ?>

        <!-- EXCERPT / SUMMARY -->
        <?php if (!empty($post['excerpt'])): ?>
            <div class="mb-10 p-6 bg-slate-50 rounded-2xl border border-slate-200 text-slate-700 text-lg md:text-xl font-medium leading-relaxed">
                <?= htmlspecialchars($post['excerpt']) ?>
            </div>
        <?php endif; ?>

        <!-- ARTICLE BODY CONTENT -->
        <div class="article-body">
            <?= $post['content'] ?>
        </div>
        
        <!-- SHARE BUTTONS -->
        <div class="mt-12 pt-8 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
            <span class="text-slate-900 font-bold text-lg">Share this article</span>
            <div class="flex items-center space-x-3">
                <a href="<?= $whatsappShareUrl ?>" target="_blank" rel="noopener"
                    class="inline-flex items-center space-x-2 px-5 py-2.5 bg-[#25D366] text-white hover:bg-[#20b958] rounded-xl text-sm font-bold transition shadow-sm">
                    <i data-lucide="share-2" class="w-4 h-4"></i>
                    <span>WhatsApp</span>
                </a>
                <button type="button" onclick="copyArticleLink('<?= $shareUrl ?>')"
                    class="inline-flex items-center space-x-2 px-5 py-2.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-xl text-sm font-bold transition cursor-pointer border border-slate-200">
                    <i data-lucide="link" class="w-4 h-4"></i>
                    <span id="copy-btn-text">Copy Link</span>
                </button>
            </div>
        </div>

        <!-- AUTHOR FOOTER BOX -->
        <div class="mt-12 bg-slate-50 p-6 sm:p-8 rounded-3xl border border-slate-200 flex flex-col sm:flex-row items-center sm:items-start text-center sm:text-left space-y-4 sm:space-y-0 sm:space-x-6">
            <div class="w-16 h-16 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-2xl shrink-0 shadow-sm">
                <?= strtoupper(substr($post['author_name'] ?: 'I', 0, 1)) ?>
            </div>
            <div>
                <h4 class="text-lg font-bold text-slate-900 mb-1">Written by <?= htmlspecialchars($post['author_name'] ?: 'ImportWale Team') ?></h4>
                <p class="text-slate-600 font-medium leading-relaxed">
                    ImportWale B2B wholesale experts providing verified insights, market guides, and professional advice for buyers.
                </p>
            </div>
        </div>
        
        <!-- B2B WHOLESALE PROMO (Minimal, professional) -->
        <div class="mt-8 bg-gradient-to-r from-slate-900 to-slate-800 p-8 rounded-3xl text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h4 class="text-xl font-bold mb-2">ImportWale B2B Wholesale</h4>
                <p class="text-slate-300 font-medium">Source direct factory wholesale items at bulk discount rates.</p>
            </div>
            <a href="<?= url('shop') ?>" class="shrink-0 px-6 py-3 bg-[#f05a29] hover:bg-orange-600 text-white font-bold rounded-xl transition shadow-md">
                Browse Catalog
            </a>
        </div>

    </article>
</main>

<!-- RELATED ARTICLES SECTION -->
<?php if (!empty($relatedPosts)): ?>
<section class="bg-slate-50 py-16 border-t border-slate-200 font-sans">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-10">
            <h3 class="text-2xl md:text-3xl font-bold text-slate-900 tracking-tight">More from our blog</h3>
            <a href="<?= url('blog') ?>" class="text-sm text-[#f05a29] font-bold hover:underline">View All &rarr;</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach ($relatedPosts as $rel): ?>
                <a href="<?= url('blog/' . $rel['slug']) ?>"
                    class="group bg-white rounded-3xl border border-slate-200 hover:border-[#f05a29] transition-all duration-300 flex flex-col overflow-hidden shadow-sm hover:shadow-xl">
                    <div class="aspect-[16/10] bg-slate-100 relative overflow-hidden">
                        <?php if (!empty($rel['featured_image'])): ?>
                            <img src="<?= asset($rel['featured_image']) ?>"
                                alt="<?= htmlspecialchars($rel['featured_image_alt'] ?: $rel['title']) ?>"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                            <div class="hidden w-full h-full flex items-center justify-center text-slate-400 bg-slate-100">
                                <i data-lucide="newspaper" class="w-8 h-8 opacity-40"></i>
                            </div>
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-slate-400 bg-slate-100">
                                <i data-lucide="newspaper" class="w-8 h-8 opacity-40"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                            <?= date('M d, Y', strtotime($rel['published_at'] ?: $rel['created_at'])) ?>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 group-hover:text-[#f05a29] transition line-clamp-2 leading-snug mb-3">
                            <?= htmlspecialchars($rel['title']) ?>
                        </h4>
                        <p class="text-sm text-slate-600 font-normal line-clamp-2 leading-relaxed flex-1">
                            <?= htmlspecialchars($rel['excerpt'] ?: mb_strimwidth(strip_tags($rel['content']), 0, 100, '...')) ?>
                        </p>
                        <div class="mt-4 pt-4 border-t border-slate-100 text-sm text-[#f05a29] font-bold">
                            Read article &rarr;
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

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