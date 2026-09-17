<?php
$title = ($post['meta_title'] ?: $post['title']) . " | ImportWale Journal";
$shareUrl = url('blog/' . $post['slug']);
$whatsappShareUrl = "https://api.whatsapp.com/send?text=" . urlencode($post['title'] . " - " . $shareUrl);
ob_start();
?>

<!-- Custom Article Content Styling for Professional Theme -->
<style>
    .article-body {
        font-size: 1rem; /* 16px */
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
        display: block;
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-collapse: collapse;
        margin: 2rem 0;
        font-size: 1rem;
        background-color: #ffffff;
        border-radius: 0.5rem;
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
        min-width: 140px;
    }
    .article-body td {
        padding: 1rem;
        border-bottom: 1px solid #e2e8f0;
        min-width: 140px;
    }
</style>

<!-- SINGLE ARTICLE CONTAINER -->
<div class="py-12 md:py-16 bg-white font-sans text-slate-900">
    <article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Article Header & Metadata -->
        <header class="text-center space-y-6 mb-10">
            <?php if (!empty($post['category_name'])): ?>
                <span class="inline-block px-4 py-1.5 bg-[#f05a29]/10 text-[#f05a29] font-bold text-xs uppercase rounded-full tracking-widest">
                    <?= htmlspecialchars($post['category_name']) ?>
                </span>
            <?php endif; ?>

            <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
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
                        <svg class="w-12 h-12 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                    </div>
                </div>
            </figure>
        <?php endif; ?>

        <!-- EXCERPT / SUMMARY -->
        <?php if (!empty($post['excerpt'])): ?>
            <div class="mb-10 p-6 bg-slate-50 rounded-2xl border border-slate-200 text-slate-700 text-base md:text-lg font-medium leading-relaxed">
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
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    <span>WhatsApp</span>
                </a>
                <button type="button" onclick="navigator.clipboard.writeText('<?= $shareUrl ?>'); alert('Link copied!');"
                    class="inline-flex items-center space-x-2 px-5 py-2.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-xl text-sm font-bold transition cursor-pointer border border-slate-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
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
            <a href="<?= url('catalog') ?>" class="shrink-0 px-6 py-3 bg-[#f05a29] hover:bg-orange-600 text-white font-bold rounded-xl transition shadow-md">
                Browse Catalog
            </a>
        </div>

    </article>
</div>

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
                                <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                            </div>
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-slate-400 bg-slate-100">
                                <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">
                            <?= date('M d, Y', strtotime($rel['published_at'] ?: $rel['created_at'])) ?>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 group-hover:text-[#f05a29] transition line-clamp-2 leading-snug mb-3">
                            <?= htmlspecialchars(htmlspecialchars_decode($rel['title'], ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?>
                        </h4>
                        <p class="text-sm text-slate-600 font-normal line-clamp-2 leading-relaxed flex-1">
                            <?= htmlspecialchars(htmlspecialchars_decode($rel['excerpt'] ?: mb_strimwidth(strip_tags($rel['content']), 0, 100, '...'), ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?>
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

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
