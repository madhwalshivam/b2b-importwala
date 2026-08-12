<?php include __DIR__ . '/../layouts/header.php'; ?>

<!-- MAIN BLOG CONTAINER -->
<main class="py-10 bg-theme-bg font-sans min-h-[75vh]">
    <div class="container mx-auto px-4 space-y-8">

        <!-- 1. TOP HEADER SECTION -->
        <div class="space-y-1">
            <h1 class="text-2xl lg:text-3xl font-semibold text-gray-900 tracking-tight">Blogs</h1>
            <p class="text-xs md:text-sm text-gray-500 font-normal">The best tips, tricks &amp; news about electric
                scooter protection, accessories &amp; maintenance</p>
        </div>

        <?php if (empty($allPublished) && empty($posts) && empty($latestPost)): ?>
            <!-- EMPTY STATE -->
            <div
                class="py-16 text-center space-y-4 max-w-md mx-auto bg-gray-50 rounded-2xl border border-gray-900 p-8 shadow-xs">
                <div
                    class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto border border-red-100 shadow-xs">
                    <i data-lucide="newspaper" class="w-8 h-8"></i>
                </div>
                <h2 class="text-base font-semibold text-gray-900">No Published Articles Found</h2>
                <p class="text-xs text-gray-500 font-normal">Check back soon for new guides, news, and electric scooter
                    tips!</p>
                <a href="<?= url('/') ?>"
                    class="inline-block px-5 py-2.5 bg-red-600 text-white font-semibold text-xs rounded-xl hover:bg-red-700 transition">
                    Return to Homepage
                </a>
            </div>
        <?php else: ?>

            <!-- 2. TOP SPLIT FEATURED SECTION (Latest Post + Editor's Picks) -->
            <?php if (empty($searchQuery) && ($pagination['current_page'] ?? 1) === 1): ?>
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <!-- LEFT COLUMN: LATEST POST (7 Columns) -->
                    <?php if (!empty($latestPost)): ?>
                        <div class="lg:col-span-7 space-y-3">
                            <h2 class="text-sm font-semibold text-gray-900 tracking-tight">Latest Post</h2>

                            <div
                                class="bg-gray-50/80 p-4 sm:p-5 rounded-2xl border border-gray-900/80 shadow-xs hover:shadow-md hover:bg-white transition group">
                                <a href="<?= url('blog/' . $latestPost['slug']) ?>"
                                    class="block rounded-xl overflow-hidden aspect-[16/9] bg-gray-100 mb-4 relative">
                                    <?php if (!empty($latestPost['featured_image'])): ?>
                                        <img src="<?= asset($latestPost['featured_image']) ?>"
                                            alt="<?= htmlspecialchars($latestPost['featured_image_alt'] ?: $latestPost['title']) ?>"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <i data-lucide="image" class="w-12 h-12"></i>
                                        </div>
                                    <?php endif; ?>
                                </a>

                                <div class="space-y-3">
                                    <h3
                                        class="text-lg md:text-xl font-semibold text-gray-900 group-hover:text-red-600 transition leading-snug line-clamp-2">
                                        <a href="<?= url('blog/' . $latestPost['slug']) ?>">
                                            <?= htmlspecialchars($latestPost['title']) ?>
                                        </a>
                                    </h3>

                                    <div class="pt-2 flex items-center justify-between text-xs text-gray-500 font-normal">
                                        <span>By <?= htmlspecialchars($latestPost['author_name'] ?: 'Mudsor Team') ?></span>
                                        <span>Published
                                            <?= date('M d, Y', strtotime($latestPost['published_at'] ?: $latestPost['created_at'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- RIGHT COLUMN: EDITOR'S PICKS (5 Columns) -->
                    <?php if (!empty($editorPicks)): ?>
                        <div class="lg:col-span-5 space-y-3">
                            <h2 class="text-sm font-semibold text-gray-900 tracking-tight">Editor's Picks</h2>

                            <div class="space-y-3">
                                <?php foreach ($editorPicks as $pick): ?>
                                    <div
                                        class="bg-gray-50/60 p-3 rounded-2xl border border-gray-900/70 hover:bg-white hover:shadow-xs transition group flex items-center space-x-3.5">
                                        <a href="<?= url('blog/' . $pick['slug']) ?>"
                                            class="w-28 md:w-32 h-20 rounded-xl overflow-hidden bg-gray-100 shrink-0 block">
                                            <?php if (!empty($pick['featured_image'])): ?>
                                                <img src="<?= asset($pick['featured_image']) ?>"
                                                    alt="<?= htmlspecialchars($pick['featured_image_alt'] ?: $pick['title']) ?>"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            <?php else: ?>
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                    <i data-lucide="image" class="w-6 h-6"></i>
                                                </div>
                                            <?php endif; ?>
                                        </a>

                                        <div class="flex-1 min-w-0 flex flex-col justify-between h-20">
                                            <div>
                                                <h4
                                                    class="text-xs font-semibold text-gray-900 group-hover:text-red-600 transition line-clamp-2 leading-snug">
                                                    <a href="<?= url('blog/' . $pick['slug']) ?>">
                                                        <?= htmlspecialchars($pick['title']) ?>
                                                    </a>
                                                </h4>
                                                <p class="text-[11px] text-gray-500 font-normal line-clamp-1 mt-0.5">
                                                    <?= htmlspecialchars($pick['excerpt'] ?: mb_strimwidth(strip_tags($pick['content']), 0, 80, '...')) ?>
                                                </p>
                                            </div>

                                            <div class="flex items-center justify-between text-[11px] text-gray-500 font-normal">
                                                <span class="truncate max-w-[110px]">By
                                                    <?= htmlspecialchars($pick['author_name'] ?: 'Mudsor') ?></span>
                                                <span><?= date('M d, Y', strtotime($pick['published_at'] ?: $pick['created_at'])) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

            <!-- 3. FILTER & SEARCH STRIP -->
            <div
                class="py-4 border-t border-b border-gray-900 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-3 w-full md:w-auto">
                    <span class="text-xs font-semibold text-gray-500">Filters:</span>
                    <a href="<?= url('blog') ?>"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition <?= empty($searchQuery) ? 'bg-slate-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-900' ?>">
                        All Articles
                    </a>
                </div>

                <!-- Search Input Bar -->
                <form action="<?= url('blog') ?>" method="GET" class="w-full md:w-80 relative flex items-center">
                    <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search..."
                        class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-900 rounded-xl text-xs font-normal text-gray-900 focus:bg-white focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-transparent transition">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3"></i>
                    <?php if (!empty($searchQuery)): ?>
                        <a href="<?= url('blog') ?>" class="absolute right-3 text-gray-400 hover:text-gray-600">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- 4. MAIN BLOG CARDS GRID (3 Columns) -->
            <div>
                <?php if (!empty($searchQuery)): ?>
                    <div class="mb-6 flex items-center justify-between">
                        <p class="text-xs font-semibold text-gray-800">
                            Search results for "<span class="text-red-600"><?= htmlspecialchars($searchQuery) ?></span>"
                            (<?= count($posts) ?> found)
                        </p>
                        <a href="<?= url('blog') ?>" class="text-xs text-red-600 font-semibold hover:underline">Clear Search
                            &rarr;</a>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($posts as $p): ?>
                        <article
                            class="bg-gray-50/50 p-4 rounded-2xl border border-gray-900/80 hover:bg-white hover:shadow-md transition flex flex-col justify-between group">
                            <div>
                                <!-- Top Image -->
                                <a href="<?= url('blog/' . $p['slug']) ?>"
                                    class="block aspect-[16/9] rounded-xl overflow-hidden bg-gray-100 mb-3">
                                    <?php if (!empty($p['featured_image'])): ?>
                                        <img src="<?= asset($p['featured_image']) ?>"
                                            alt="<?= htmlspecialchars($p['featured_image_alt'] ?: $p['title']) ?>"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                            loading="lazy">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <i data-lucide="image" class="w-8 h-8"></i>
                                        </div>
                                    <?php endif; ?>
                                </a>

                                <!-- Card Title -->
                                <h3
                                    class="text-sm font-semibold text-gray-900 group-hover:text-red-600 transition line-clamp-2 leading-snug mb-2">
                                    <a href="<?= url('blog/' . $p['slug']) ?>">
                                        <?= htmlspecialchars($p['title']) ?>
                                    </a>
                                </h3>

                                <p class="text-xs text-gray-500 font-normal line-clamp-2 leading-relaxed mb-4">
                                    <?= htmlspecialchars($p['excerpt'] ?: mb_strimwidth(strip_tags($p['content']), 0, 100, '...')) ?>
                                </p>
                            </div>

                            <!-- Bottom Metadata Row -->
                            <div
                                class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-normal">
                                <span>By <?= htmlspecialchars($p['author_name'] ?: 'Mudsor') ?></span>
                                <span><?= date('M d, Y', strtotime($p['published_at'] ?: $p['created_at'])) ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 5. PAGINATION -->
            <?php if (!empty($pagination) && $pagination['last_page'] > 1): ?>
                <div class="pt-6 border-t border-gray-900 flex items-center justify-center space-x-2">
                    <?php if ($pagination['current_page'] > 1): ?>
                        <a href="<?= url('blog?page=' . ($pagination['current_page'] - 1) . (!empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '')) ?>"
                            class="px-4 py-2 bg-white border border-gray-900 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50 transition flex items-center space-x-1">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            <span>Previous</span>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                        <a href="<?= url('blog?page=' . $i . (!empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '')) ?>"
                            class="w-9 h-9 flex items-center justify-center rounded-xl text-xs font-semibold <?= $i === $pagination['current_page'] ? 'bg-slate-900 text-white shadow-xs' : 'bg-white border border-gray-900 text-gray-700 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                        <a href="<?= url('blog?page=' . ($pagination['current_page'] + 1) . (!empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '')) ?>"
                            class="px-4 py-2 bg-white border border-gray-900 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50 transition flex items-center space-x-1">
                            <span>Next</span>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>


        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/../layouts/footer.php'; ?>