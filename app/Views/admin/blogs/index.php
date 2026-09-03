<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="space-y-6">

    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 text-[11px] font-bold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Content Management
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Articles &amp; SEO</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1 tracking-tight">Blog &amp; Journal CMS</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Manage ImportWale blog articles, categories, scheduled posts, and SEO meta tags.
            </p>
        </div>
        <div>
            <a href="<?= url('admin/blogs/create') ?>" class="inline-flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition duration-150 cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add New Article</span>
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
            </div>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-2 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
            <a href="<?= url('admin/blogs') ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold transition <?= empty($statusFilter) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                All Posts
            </a>
            <a href="<?= url('admin/blogs?status=published' . (!empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '')) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold transition <?= $statusFilter === 'published' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                Published
            </a>
            <a href="<?= url('admin/blogs?status=draft' . (!empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '')) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold transition <?= $statusFilter === 'draft' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                Drafts
            </a>
            <a href="<?= url('admin/blogs?status=scheduled' . (!empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '')) ?>" class="px-3 py-1.5 rounded-lg text-xs font-bold transition <?= $statusFilter === 'scheduled' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                Scheduled
            </a>
        </div>

        <form action="<?= url('admin/blogs') ?>" method="GET" class="w-full md:w-80 flex items-center">
            <?php if (!empty($statusFilter)): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <?php endif; ?>
            <div class="relative w-full">
                <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search by title or author..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
            </div>
        </form>
    </div>

    <!-- Posts Data Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 border-collapse">
                <thead class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Article</th>
                        <th class="px-5 py-3.5">Category</th>
                        <th class="px-5 py-3.5">Author</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Views</th>
                        <th class="px-5 py-3.5">Published Date</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center space-y-2">
                                    <i data-lucide="file-text" class="w-8 h-8 text-slate-300"></i>
                                    <p class="font-semibold text-slate-500">No blog posts found.</p>
                                    <a href="<?= url('admin/blogs/create') ?>" class="text-red-600 hover:underline font-bold text-xs mt-1">Create your first article &rarr;</a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $p): ?>
                            <tr class="hover:bg-slate-50/70 transition duration-150">
                                <td class="px-5 py-4">
                                    <div class="flex items-center space-x-3.5">
                                        <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                                            <?php if (!empty($p['featured_image'])): ?>
                                                <img src="<?= asset($p['featured_image']) ?>" alt="<?= htmlspecialchars($p['featured_image_alt'] ?? $p['title']) ?>" class="w-full h-full object-cover">
                                            <?php else: ?>
                                                <i data-lucide="image" class="w-5 h-5 text-slate-400 opacity-40"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="<?= url('admin/blogs/edit/' . $p['id']) ?>" class="font-bold text-slate-900 hover:text-red-600 transition line-clamp-1">
                                                <?= htmlspecialchars($p['title']) ?>
                                            </a>
                                            <div class="text-[11px] text-slate-400 font-mono line-clamp-1 mt-0.5">/blog/<?= htmlspecialchars($p['slug']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <?php if (!empty($p['category_name'])): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200/60">
                                            <?= htmlspecialchars($p['category_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-xs">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 font-semibold text-slate-700">
                                    <?= htmlspecialchars($p['author_name'] ?: 'ImportWale Team') ?>
                                </td>
                                <td class="px-5 py-4">
                                    <?php if ($p['status'] === 'published'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Published
                                        </span>
                                    <?php elseif ($p['status'] === 'scheduled'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span> Scheduled
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-1.5"></span> Draft
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4 text-slate-600 font-mono text-xs">
                                    <?= number_format((int)($p['views'] ?? 0)) ?>
                                </td>
                                <td class="px-5 py-4 text-slate-500 text-[11px]">
                                    <div><?= date('M d, Y', strtotime($p['published_at'] ?: $p['created_at'])) ?></div>
                                    <div class="text-slate-400 text-[10px]"><?= date('h:i A', strtotime($p['published_at'] ?: $p['created_at'])) ?></div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <a href="<?= url('blog/' . $p['slug']) ?>" target="_blank" title="Preview Article" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition">
                                            <i data-lucide="external-link" class="w-4 h-4"></i>
                                        </a>
                                        <a href="<?= url('admin/blogs/edit/' . $p['id']) ?>" title="Edit Article" class="p-1.5 text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        <a href="<?= url('admin/blogs/delete/' . $p['id']) ?>" onclick="return confirm('Are you sure you want to delete this blog post? The database record and uploaded image file will be permanently removed.')" title="Delete Article" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (!empty($pagination) && $pagination['last_page'] > 1): ?>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <span class="text-xs text-slate-500 font-medium">
                    Page <?= $pagination['current_page'] ?> of <?= $pagination['last_page'] ?> (Total: <?= $pagination['total'] ?> articles)
                </span>
                <div class="flex items-center space-x-1">
                    <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
                        <a href="<?= url('admin/blogs?page=' . $i . (!empty($statusFilter) ? '&status=' . $statusFilter : '') . (!empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '')) ?>" class="px-3 py-1 rounded-lg text-xs font-bold <?= $i === $pagination['current_page'] ? 'bg-red-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
