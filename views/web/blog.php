<?php
$title = $seoOptions['title'] ?? "ImportWale Journal — Insights, Guides & B2B News";
ob_start();
?>

<div style="margin-top: 24px; margin-bottom: 32px;">

  <!-- Journal Header Section (Light Theme with Navbar Gap) -->
  <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #e5e7eb;">
    <span style="color: #f05a29; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">
      ImportWale Journal
    </span>
    <h1 style="font-size: 32px; font-weight: 800; color: #111827; margin: 0 0 8px 0; letter-spacing: -0.5px;">
      B2B Wholesale News &amp; Market Insights
    </h1>
    <p style="font-size: 14px; color: #6b7280; margin: 0; max-width: 700px; line-height: 1.5;">
      Expert guides, market trends, product comparisons, and sourcing tips for wholesale buyers.
    </p>
  </div>

  <!-- Filter Strip & Search Bar -->
  <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 28px; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;">
    <!-- Category Tabs -->
    <div style="display: flex; align-items: center; gap: 8px; overflow-x: auto; max-width: 100%;">
      <a href="<?= url('blog' . (!empty($searchQuery) ? '?q=' . urlencode($searchQuery) : '')) ?>" 
         style="padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; transition: all 0.2s; <?= empty($activeCategory) ? 'background: #f05a29; color: #fff;' : 'background: #f3f4f6; color: #374151;' ?>">
        All Articles
      </a>
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $cat): ?>
          <a href="<?= url('blog?cat=' . $cat['slug'] . (!empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '')) ?>" 
             style="padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; transition: all 0.2s; <?= ($activeCategory === $cat['slug']) ? 'background: #f05a29; color: #fff;' : 'background: #f3f4f6; color: #374151;' ?>">
            <?= htmlspecialchars($cat['name']) ?>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Search Input -->
    <form action="<?= url('blog') ?>" method="GET" style="position: relative; display: flex; align-items: center; width: 280px; max-width: 100%;">
      <?php if (!empty($activeCategory)): ?>
        <input type="hidden" name="cat" value="<?= htmlspecialchars($activeCategory) ?>">
      <?php endif; ?>
      <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search journal..." 
             style="width: 100%; padding: 9px 14px; padding-right: 36px; background: #fff; border: 1px solid #d1d5db; border-radius: 10px; font-size: 13px; color: #111827; outline: none;">
      <button type="submit" style="position: absolute; right: 10px; background: none; border: none; cursor: pointer; color: #6b7280; display: flex; align-items: center; justify-content: center;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </button>
    </form>
  </div>

  <!-- 3-Column Articles Grid -->
  <?php if (empty($posts)): ?>
    <div style="text-align: center; padding: 60px 20px; background: #f9fafb; border-radius: 16px; border: 1px solid #e5e7eb; margin-bottom: 40px;">
      <h3 style="font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 8px;">No Articles Found</h3>
      <p style="font-size: 14px; color: #6b7280; margin-bottom: 16px;">We couldn't find any posts matching your search query.</p>
      <a href="<?= url('blog') ?>" style="display: inline-block; padding: 10px 20px; background: #f05a29; color: #fff; text-decoration: none; border-radius: 10px; font-size: 13px; font-weight: 700;">View All Articles</a>
    </div>
  <?php else: ?>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 24px; margin-bottom: 40px;">
      <?php foreach ($posts as $p): ?>
        <a href="<?= url('blog/' . $p['slug']) ?>" style="display: flex; flex-direction: column; justify-content: space-between; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 16px; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.05);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.05)';">
          <div>
            <!-- Thumbnail Image Container -->
            <div style="aspect-ratio: 16/9; width: 100%; border-radius: 12px; overflow: hidden; background: #f3f4f6; position: relative; margin-bottom: 12px;">
              <?php if (!empty($p['featured_image'])): ?>
                <img src="<?= asset($p['featured_image']) ?>" alt="<?= htmlspecialchars($p['featured_image_alt'] ?: $p['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center; background: #f3f4f6; color: #9ca3af;">
                  <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
              <?php else: ?>
                <div style="display: flex; width: 100%; height: 100%; align-items: center; justify-content: center; background: #f3f4f6; color: #9ca3af;">
                  <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
              <?php endif; ?>

              <?php if (!empty($p['category_name'])): ?>
                <span style="position: absolute; top: 10px; left: 10px; background: rgba(255,255,255,0.92); color: #111827; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; border: 1px solid #e5e7eb;">
                  <?= htmlspecialchars($p['category_name']) ?>
                </span>
              <?php endif; ?>
            </div>

            <!-- Post Title -->
            <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 8px 0; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
              <?= htmlspecialchars(htmlspecialchars_decode($p['title'], ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?>
            </h3>

            <!-- Post Excerpt -->
            <p style="font-size: 13px; color: #6b7280; margin: 0; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
              <?= htmlspecialchars($p['excerpt'] ?: mb_strimwidth(strip_tags($p['content']), 0, 110, '...')) ?>
            </p>
          </div>

          <!-- Card Footer Meta -->
          <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 16px; padding-top: 12px; border-top: 1px solid #f3f4f6; font-size: 12px; color: #9ca3af; font-weight: 500;">
            <span>By <?= htmlspecialchars($p['author_name'] ?: 'ImportWale Team') ?></span>
            <span><?= date('M d, Y', strtotime($p['published_at'] ?: $p['created_at'])) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Pagination Controls -->
    <?php if (!empty($pagination) && $pagination['last_page'] > 1): ?>
      <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 40px;">
        <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
          <a href="<?= url('blog?page=' . $i . (!empty($activeCategory) ? '&cat=' . $activeCategory : '') . (!empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '')) ?>" 
             style="width: 36px; height: 36px; border-radius: 10px; font-size: 13px; font-weight: 700; display: flex; align-items: center; justify-content: center; text-decoration: none; <?= $i === $pagination['current_page'] ? 'background: #f05a29; color: #fff;' : 'background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb;' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
