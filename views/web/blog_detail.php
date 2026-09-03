<?php
$title = ($post['meta_title'] ?: $post['title']) . " | ImportWale Journal";
$shareUrl = url('blog/' . $post['slug']);
$whatsappShareUrl = "https://api.whatsapp.com/send?text=" . urlencode($post['title'] . " - " . $shareUrl);
ob_start();
?>

<!-- Custom Article Content Styling -->
<style>
  .article-body h1 { font-size: 26px; font-weight: 800; color: #111827; margin-top: 24px; margin-bottom: 12px; line-height: 1.3; }
  .article-body h2 { font-size: 20px; font-weight: 700; color: #1f2937; margin-top: 20px; margin-bottom: 10px; line-height: 1.35; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; }
  .article-body h3 { font-size: 17px; font-weight: 600; color: #374151; margin-top: 16px; margin-bottom: 8px; line-height: 1.4; }
  .article-body p { margin-bottom: 18px; line-height: 1.7; color: #374151; font-size: 15px; }
  .article-body ul, .article-body ol { padding-left: 20px; margin-bottom: 18px; color: #374151; }
  .article-body li { margin-bottom: 6px; font-size: 15px; line-height: 1.6; }
  .article-body a { color: #f05a29; font-weight: 600; text-decoration: underline; text-underline-offset: 3px; }
  .article-body blockquote { border-left: 4px solid #f05a29; padding: 12px 18px; font-style: italic; color: #1f2937; margin: 20px 0; background-color: #f9fafb; border-radius: 0 10px 10px 0; }
  .article-body img { max-width: 100%; height: auto; border-radius: 12px; margin: 20px auto; display: block; border: 1px solid #e5e7eb; }
  .article-body table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; background-color: #ffffff; border-radius: 10px; overflow: hidden; border: 1px solid #e5e7eb; }
  .article-body th { background-color: #f9fafb; font-weight: 700; color: #111827; text-align: left; padding: 10px 14px; border-bottom: 2px solid #d1d5db; }
  .article-body td { padding: 10px 14px; border-bottom: 1px solid #e5e7eb; color: #374151; }
</style>

<div style="margin-top: 24px; margin-bottom: 32px;">

  <!-- Breadcrumbs -->
  <div style="font-size: 13px; color: #6b7280; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
    <a href="<?= url('/') ?>" style="color: #6b7280; text-decoration: none;">Home</a>
    <span>/</span>
    <a href="<?= url('blog') ?>" style="color: #6b7280; text-decoration: none;">Blog</a>
    <span>/</span>
    <span style="color: #111827; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;"><?= htmlspecialchars($post['title']) ?></span>
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px; margin-bottom: 50px;">
    
    <!-- Left Main Column (Article) -->
    <article style="grid-column: span 8 / span 8;">
      
      <!-- Title & Category Badge -->
      <div style="margin-bottom: 20px;">
        <?php if (!empty($post['category_name'])): ?>
          <span style="background: rgba(240, 90, 41, 0.1); color: #f05a29; border: 1px solid rgba(240, 90, 41, 0.2); padding: 4px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; margin-bottom: 10px;">
            <?= htmlspecialchars($post['category_name']) ?>
          </span>
        <?php endif; ?>

        <h1 style="font-size: 28px; font-weight: 800; color: #111827; margin: 0 0 12px 0; line-height: 1.3;">
          <?= htmlspecialchars(htmlspecialchars_decode($post['title'], ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?>
        </h1>

        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 0; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; font-size: 13px; color: #6b7280;">
          <div>
            By <strong style="color: #111827;"><?= htmlspecialchars($post['author_name'] ?: 'ImportWale Team') ?></strong> &bull; <?= date('F d, Y', strtotime($post['published_at'] ?: $post['created_at'])) ?>
          </div>

          <div style="display: flex; align-items: center; gap: 8px;">
            <a href="<?= $whatsappShareUrl ?>" target="_blank" rel="noopener" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none;">
              <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              WhatsApp Share
            </a>
            <button type="button" onclick="navigator.clipboard.writeText('<?= $shareUrl ?>'); alert('Link copied to clipboard!');" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer;">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
              Copy Link
            </button>
          </div>
        </div>
      </div>

      <!-- Featured Image -->
      <?php if (!empty($post['featured_image'])): ?>
        <div style="aspect-ratio: 16/9; width: 100%; border-radius: 16px; overflow: hidden; background: #f3f4f6; margin-bottom: 24px; border: 1px solid #e5e7eb;">
          <img src="<?= asset($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['featured_image_alt'] ?: $post['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none';">
        </div>
      <?php endif; ?>

      <!-- Excerpt Box -->
      <?php if (!empty($post['excerpt'])): ?>
        <div style="background: #f9fafb; border-left: 4px solid #f05a29; padding: 14px 18px; border-radius: 0 12px 12px 0; font-size: 14px; color: #374151; font-style: italic; margin-bottom: 24px; border-top: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
          <?= htmlspecialchars($post['excerpt']) ?>
        </div>
      <?php endif; ?>

      <!-- Body HTML Content -->
      <div class="article-body">
        <?= $post['content'] ?>
      </div>

      <!-- Related Posts -->
      <?php if (!empty($relatedPosts)): ?>
        <div style="margin-top: 40px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
          <h3 style="font-size: 18px; font-weight: 700; color: #111827; margin-bottom: 16px;">Related Articles</h3>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <?php foreach ($relatedPosts as $rel): ?>
              <a href="<?= url('blog/' . $rel['slug']) ?>" style="display: block; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px; text-decoration: none;">
                <div style="aspect-ratio: 16/9; border-radius: 8px; overflow: hidden; background: #f3f4f6; margin-bottom: 8px;">
                  <?php if (!empty($rel['featured_image'])): ?>
                    <img src="<?= asset($rel['featured_image']) ?>" alt="<?= htmlspecialchars($rel['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                  <?php endif; ?>
                </div>
                <h4 style="font-size: 13px; font-weight: 700; color: #111827; margin: 0 0 4px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($rel['title']) ?></h4>
                <span style="font-size: 11px; color: #f05a29; font-weight: 700;">Read Article &rarr;</span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    </article>

    <!-- Right Sidebar -->
    <aside style="grid-column: span 4 / span 4;">
      <?php if (!empty($recentPosts)): ?>
        <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 20px; margin-bottom: 24px;">
          <h3 style="font-size: 14px; font-weight: 700; color: #111827; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #f3f4f6;">
            Recent Articles
          </h3>
          <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php foreach ($recentPosts as $rp): ?>
              <a href="<?= url('blog/' . $rp['slug']) ?>" style="display: flex; gap: 10px; text-decoration: none;">
                <div style="width: 56px; height: 56px; border-radius: 8px; overflow: hidden; background: #f3f4f6; flex-shrink: 0;">
                  <?php if (!empty($rp['featured_image'])): ?>
                    <img src="<?= asset($rp['featured_image']) ?>" alt="<?= htmlspecialchars($rp['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                  <?php endif; ?>
                </div>
                <div>
                  <span style="font-size: 10px; color: #9ca3af;"><?= date('M d, Y', strtotime($rp['published_at'] ?: $rp['created_at'])) ?></span>
                  <h4 style="font-size: 12px; font-weight: 700; color: #111827; margin: 2px 0 0 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($rp['title']) ?></h4>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Wholesale CTA Box (Light Theme) -->
      <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; text-align: center;">
        <h4 style="font-size: 16px; font-weight: 800; margin: 0 0 8px 0; color: #111827;">ImportWale B2B Sourcing</h4>
        <p style="font-size: 12px; color: #6b7280; margin: 0 0 16px 0;">Direct factory prices &amp; bulk wholesale catalog.</p>
        <a href="<?= url('catalog') ?>" style="display: block; padding: 10px 16px; background: #f05a29; color: #fff; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none;">Explore Catalog</a>
      </div>
    </aside>

  </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
