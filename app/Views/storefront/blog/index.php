<?php include __DIR__ . '/../layouts/header.php'; ?>

<!-- MAIN BLOG CONTAINER (Light Theme Matched - White & Orange Brand Palette) -->
<main class="py-10 bg-white font-sans min-h-[75vh] text-slate-900">
    <div class="container mx-auto px-4 space-y-8">

        <!-- 1. TOP HEADER SECTION -->
        <div class="space-y-2 border-b border-gray-200 pb-6">
            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">ImportWale Journal</h1>
            <p class="text-xs md:text-sm text-slate-600 font-normal max-w-3xl">
                B2B wholesale guides, jewellery industry trends, product insights, and updates from ImportWale.
            </p>
        </div>

        <!-- 2. CATEGORY FILTER TABS & SEARCH STRIP -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 py-2 border-b border-gray-200">
            <!-- Category Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto w-full md:w-auto pb-1 md:pb-0 scrollbar-none">
                <a href="<?= url('blog' . (!empty($searchQuery) ? '?q=' . urlencode($searchQuery) : '')) ?>"
                    class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer <?= empty($activeCategory) ? 'bg-[#f05a29] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                    All Articles
                </a>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <a href="<?= url('blog?cat=' . $cat['slug'] . (!empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '')) ?>"
                            class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition cursor-pointer <?= ($activeCategory === $cat['slug']) ? 'bg-[#f05a29] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Keyword Search Input -->
            <form action="<?= url('blog') ?>" method="GET" class="w-full md:w-80 relative flex items-center shrink-0">
                <?php if (!empty($activeCategory)): ?>
                    <input type="hidden" name="cat" value="<?= htmlspecialchars($activeCategory) ?>">
                <?php endif; ?>
                <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search journal..."
                    class="w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-none focus:border-[#f05a29] transition">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3"></i>
                <?php if (!empty($searchQuery)): ?>
                    <a href="<?= url('blog' . (!empty($activeCategory) ? '?cat=' . $activeCategory : '')) ?>" class="absolute right-3 text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($posts)): ?>
            <!-- EMPTY STATE -->
            <div class="py-16 text-center space-y-4 max-w-md mx-auto bg-slate-50 rounded-2xl border border-slate-200 p-8">
                <div class="w-14 h-14 bg-[#f05a29]/10 text-[#f05a29] rounded-2xl flex items-center justify-center mx-auto border border-[#f05a29]/20">
                    <i data-lucide="newspaper" class="w-7 h-7"></i>
                </div>
                <h2 class="text-base font-bold text-slate-900">No Articles Found</h2>
                <p class="text-xs text-slate-500 font-normal">No blog articles matched your filter criteria.</p>
                <a href="<?= url('blog') ?>" class="inline-block px-5 py-2.5 bg-[#f05a29] text-white font-bold text-xs rounded-xl hover:bg-orange-600 transition shadow-xs">
                    Clear Filters &amp; View All
                </a>
            </div>
        <?php else: ?>

            <?php if (!empty($searchQuery)): ?>
                <div class="flex items-center justify-between text-xs font-semibold text-slate-600">
                    <span>Search results for "<span class="text-[#f05a29]"><?= htmlspecialchars($searchQuery) ?></span>" (<?= $pagination['total'] ?> found)</span>
                    <a href="<?= url('blog') ?>" class="text-[#f05a29] hover:underline">Clear Search &rarr;</a>
                </div>
            <?php endif; ?>

            <!-- 3. MAIN BLOG POSTS GRID (3 cols desktop, 2 cols tablet, 1 col mobile) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <?php foreach ($posts as $p): ?>
                    <a href="<?= url('blog/' . $p['slug']) ?>" class="group block bg-white hover:bg-white rounded-2xl border border-slate-200/80 hover:border-[#f05a29] shadow-xs hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col justify-between">
                        <div class="space-y-3.5 p-4 sm:p-5">
                            <!-- Thumbnail Image Container (With onError Graceful Fallback) -->
                            <div class="aspect-[16/9] rounded-xl overflow-hidden bg-slate-100 relative border border-slate-100">
                                <?php if (!empty($p['featured_image'])): ?>
                                    <img src="<?= asset($p['featured_image']) ?>"
                                        alt="<?= htmlspecialchars($p['featured_image_alt'] ?: $p['title']) ?>"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');"
                                        loading="lazy">
                                    <div class="hidden w-full h-full flex items-center justify-center text-slate-400 bg-slate-100">
                                        <i data-lucide="newspaper" class="w-8 h-8 opacity-40"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-slate-400 bg-slate-100">
                                        <i data-lucide="newspaper" class="w-8 h-8 opacity-40"></i>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($p['category_name'])): ?>
                                    <span class="absolute top-2.5 left-2.5 px-2.5 py-1 bg-white/90 backdrop-blur-xs text-slate-800 text-[10px] font-bold rounded-lg border border-slate-200 shadow-xs">
                                        <?= htmlspecialchars($p['category_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Title -->
                            <h3 class="text-base font-bold text-slate-900 group-hover:text-[#f05a29] transition-colors line-clamp-2 leading-snug">
                                <?= htmlspecialchars(htmlspecialchars_decode($p['title'], ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?>
                            </h3>

                            <!-- Excerpt -->
                            <p class="text-xs text-slate-600 font-normal line-clamp-2 leading-relaxed">
                                <?= htmlspecialchars($p['excerpt'] ?: mb_strimwidth(strip_tags($p['content']), 0, 110, '...')) ?>
                            </p>
                        </div>

                        <!-- Card Footer -->
                        <div class="p-4 sm:p-5 pt-3 mt-auto border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
                            <span class="truncate max-w-[130px]">By <?= htmlspecialchars($p['author_name'] ?: 'ImportWale Team') ?></span>
                            <span><?= date('M d, Y', strtotime($p['published_at'] ?: $p['created_at'])) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- 4. PAGINATION CONTROLS -->
            <?php if (!empty($pagination) && $pagination['last_page'] > 1): ?>
                <div class="pt-6 border-t border-slate-200 flex items-center justify-center space-x-2">
                    <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                        <a href="<?= url('blog?page=' . $i . (!empty($activeCategory) ? '&cat=' . $activeCategory : '') . (!empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '')) ?>"
                            class="w-9 h-9 rounded-xl text-xs font-bold flex items-center justify-center transition <?= $i === $pagination['current_page'] ? 'bg-[#f05a29] text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/../layouts/footer.php'; ?>