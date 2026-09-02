<?php
$title = $pageTitle ?? "Verified Customer Reviews & Buyer Testimonials | ImportWale Wholesale";
ob_start();
?>

<div class="reviews-page-wrapper">
  
  <!-- Hero Summary Header -->
  <div class="reviews-hero-header">
    <div class="reviews-hero-content">
      <div class="reviews-trust-pill">
        <span class="star-icon">★</span>
        <span>Verified Buyer Feedback</span>
      </div>

      <h1 class="reviews-main-title">What Indian & Global Buyers Say About ImportWale</h1>
      <p class="reviews-sub-title">
        Real feedback from wholesale buyers, retailers, corporate gift suppliers, and distributors sourcing directly with factory prices from ImportWale.
      </p>

      <!-- Rating Stats Summary Card -->
      <div class="reviews-stats-card">
        <div class="stat-score-block">
          <div class="score-number"><?= number_format($avgRating, 1) ?></div>
          <div class="score-stars">
            <?php for ($s = 1; $s <= 5; $s++): ?>
              <span class="star-box active">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="#FFFFFF" class="no-size-reset">
                  <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>
              </span>
            <?php endfor; ?>
          </div>
        </div>

        <div class="stat-divider"></div>

        <div class="stat-meta-block">
          <div class="stat-count-text">Based on <strong><?= $totalReviews ?> verified buyer reviews</strong></div>
          <div class="stat-amber-text">★ 100% Authentic Customer & Storefront Verified Reviews</div>
        </div>
      </div>
    </div>
  </div>

  <!-- All Reviews Grid Section -->
  <div class="reviews-grid-section">
    <?php if (empty($testimonials)): ?>
      <div class="empty-reviews-box">
        <p>No customer reviews available yet.</p>
      </div>
    <?php else: ?>
      <div class="reviews-cards-grid">
        <?php foreach ($testimonials as $item): ?>
          <div class="everful-review-card page-card">
            
            <!-- Top Rating Stars (Amber Star Boxes - NO GREEN) -->
            <div class="everful-rating-row">
              <?php for ($s = 1; $s <= 5; $s++): ?>
                <span class="everful-star-box <?= $s <= $item['rating'] ? 'active' : '' ?>">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="#FFFFFF" class="no-size-reset">
                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                  </svg>
                </span>
              <?php endfor; ?>
            </div>

            <!-- Full Review Text -->
            <p class="everful-review-text page-text">
              "<?= htmlspecialchars($item['review_text']) ?>"
            </p>

            <!-- Linked Product Tag (if available) -->
            <?php if (!empty($item['product_name']) && !empty($item['product_slug'])): ?>
              <div class="linked-product-chip">
                <span class="chip-label">Purchased Item:</span>
                <a href="<?= url('product/' . htmlspecialchars($item['product_slug'])) ?>" class="chip-link">
                  <?= htmlspecialchars($item['product_name']) ?> &rarr;
                </a>
              </div>
            <?php endif; ?>

            <!-- Reviewer Details (Photo ONLY if uploaded, NO initial letter circle) -->
            <div class="everful-reviewer-info">
              <?php if (!empty($item['photo_path'])): ?>
                <img src="<?= url(ltrim($item['photo_path'], '/')) ?>" 
                     alt="<?= htmlspecialchars($item['reviewer_name']) ?>" 
                     class="everful-avatar-img">
              <?php endif; ?>

              <div class="everful-reviewer-meta">
                <div class="everful-reviewer-name"><?= htmlspecialchars($item['reviewer_name']) ?></div>
                <div class="everful-reviewer-location"><?= htmlspecialchars($item['location']) ?></div>
              </div>
            </div>

          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Bottom CTA Box -->
  <div class="reviews-bottom-cta">
    <h3>Ready to experience effortless B2B wholesale sourcing?</h3>
    <p>Join thousands of businesses sourcing directly with factory-direct prices and fast pan-India & global delivery.</p>
    <a href="<?= url('catalog') ?>" class="cta-explore-btn">Explore Wholesale Catalog &rarr;</a>
  </div>

</div>

<!-- Page CSS -->
<style>
.reviews-page-wrapper {
  max-width: 1280px !important;
  margin: 0 auto !important;
  padding: 32px 16px 64px 16px !important;
  font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
}

/* Hero Section */
.reviews-hero-header {
  background: #F8FAFC !important;
  border: 1px solid #E2E8F0 !important;
  border-radius: 24px !important;
  padding: 48px 32px !important;
  text-align: center !important;
  margin-bottom: 48px !important;
}

.reviews-hero-content {
  max-width: 760px !important;
  margin: 0 auto !important;
}

.reviews-trust-pill {
  display: inline-flex !important;
  align-items: center !important;
  gap: 6px !important;
  background: #FFF5F2 !important;
  color: #f05a29 !important;
  border: 1px solid #FDE8E0 !important;
  font-size: 13px !important;
  font-weight: 700 !important;
  padding: 6px 18px !important;
  border-radius: 99px !important;
  margin-bottom: 18px !important;
}

.reviews-main-title {
  font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
  font-size: 32px !important;
  font-weight: 600 !important;
  color: #0F172A !important;
  margin: 0 0 16px 0 !important;
  line-height: 1.25 !important;
  letter-spacing: -0.01em !important;
}

.reviews-sub-title {
  font-size: 15.5px !important;
  color: #475569 !important;
  line-height: 1.6 !important;
  margin: 0 0 32px 0 !important;
}

/* Stats Summary Card */
.reviews-stats-card {
  display: inline-flex !important;
  align-items: center !important;
  gap: 24px !important;
  background: #FFFFFF !important;
  padding: 18px 32px !important;
  border-radius: 18px !important;
  box-shadow: none !important;
  border: 1px solid #E2E8F0 !important;
  text-align: left !important;
}

.stat-score-block {
  display: flex !important;
  align-items: center !important;
  gap: 12px !important;
}

.score-number {
  font-size: 34px !important;
  font-weight: 800 !important;
  color: #0F172A !important;
  line-height: 1 !important;
}

.score-stars {
  display: flex !important;
  gap: 4px !important;
}

.score-stars .star-box {
  width: 22px !important;
  min-width: 22px !important;
  max-width: 22px !important;
  height: 22px !important;
  min-height: 22px !important;
  max-height: 22px !important;
  background: #f05a29 !important;
  color: #FFFFFF !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  border-radius: 4px !important;
  flex-shrink: 0 !important;
}

.stat-divider {
  width: 1px !important;
  height: 44px !important;
  background: #E2E8F0 !important;
}

.stat-count-text {
  font-size: 14px !important;
  color: #0F172A !important;
}

.stat-amber-text {
  font-size: 12.5px !important;
  color: #f05a29 !important;
  font-weight: 600 !important;
  margin-top: 3px !important;
}

/* Reviews Grid */
.reviews-cards-grid {
  display: grid !important;
  grid-template-columns: repeat(3, 1fr) !important;
  gap: 24px !important;
}

.everful-review-card.page-card {
  min-height: auto !important;
  background: #FFFFFF !important;
  border: 1px solid #E2E8F0 !important;
  border-radius: 16px !important;
  padding: 24px !important;
  box-shadow: none !important;
  display: flex !important;
  flex-direction: column !important;
  justify-content: space-between !important;
  transition: all 0.22s ease !important;
}

.everful-review-card.page-card:hover {
  transform: translateY(-3px) !important;
  box-shadow: none !important;
  border-color: #CBD5E1 !important;
}

/* Rating Boxes */
.everful-rating-row {
  display: flex !important;
  gap: 4px !important;
  margin-bottom: 16px !important;
  align-items: center !important;
}

.everful-star-box {
  width: 22px !important;
  min-width: 22px !important;
  max-width: 22px !important;
  height: 22px !important;
  min-height: 22px !important;
  max-height: 22px !important;
  background: #CBD5E1 !important;
  color: #FFFFFF !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  border-radius: 4px !important;
  flex-shrink: 0 !important;
}

.everful-star-box.active {
  background: #f05a29 !important;
}

.everful-review-text.page-text {
  -webkit-line-clamp: unset !important;
  overflow: visible !important;
  display: block !important;
  font-size: 14px !important;
  line-height: 1.6 !important;
  color: #334155 !important;
  margin-bottom: 18px !important;
  font-weight: 400 !important;
}

/* Linked Product Chip */
.linked-product-chip {
  background: #F8FAFC !important;
  border: 1px solid #E2E8F0 !important;
  border-radius: 8px !important;
  padding: 8px 12px !important;
  font-size: 12px !important;
  margin-bottom: 16px !important;
  display: flex !important;
  align-items: center !important;
  gap: 6px !important;
}

.chip-label {
  color: #64748B !important;
  font-weight: 500 !important;
}

.chip-link {
  color: #f05a29 !important;
  font-weight: 600 !important;
  text-decoration: none !important;
}

.chip-link:hover {
  text-decoration: underline !important;
}

/* Reviewer Info */
.everful-reviewer-info {
  display: flex !important;
  align-items: center !important;
  gap: 12px !important;
  width: 100% !important;
  margin-top: auto !important;
}

.everful-avatar-img {
  width: 42px !important;
  min-width: 42px !important;
  max-width: 42px !important;
  height: 42px !important;
  min-height: 42px !important;
  max-height: 42px !important;
  border-radius: 50% !important;
  object-fit: cover !important;
  flex-shrink: 0 !important;
  border: 1px solid #E2E8F0 !important;
}

.everful-reviewer-meta {
  display: flex !important;
  flex-direction: column !important;
  min-width: 0 !important;
}

.everful-reviewer-name {
  font-size: 15px !important;
  font-weight: 700 !important;
  color: #0F172A !important;
  line-height: 1.25 !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}

.everful-reviewer-location {
  font-size: 12.5px !important;
  font-weight: 500 !important;
  color: #64748B !important;
  margin-top: 2px !important;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
}

/* Bottom CTA */
.reviews-bottom-cta {
  margin-top: 64px !important;
  background: #0F172A !important;
  color: #FFFFFF !important;
  border-radius: 20px !important;
  padding: 48px 32px !important;
  text-align: center !important;
}

.reviews-bottom-cta h3 {
  font-size: 24px !important;
  font-weight: 700 !important;
  margin: 0 0 12px 0 !important;
  color: #FFFFFF !important;
  font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
}

.reviews-bottom-cta p {
  font-size: 15px !important;
  color: #94A3B8 !important;
  margin: 0 0 24px 0 !important;
}

.cta-explore-btn {
  display: inline-block !important;
  padding: 13px 30px !important;
  background: #f05a29 !important;
  color: #FFFFFF !important;
  font-weight: 700 !important;
  font-size: 14px !important;
  border-radius: 12px !important;
  text-decoration: none !important;
  transition: all 0.22s ease !important;
  box-shadow: 0 4px 14px rgba(240, 90, 41, 0.3) !important;
}

.cta-explore-btn:hover {
  background: #d8481b !important;
  transform: translateY(-2px) !important;
}

@media (max-width: 1024px) {
  .reviews-cards-grid {
    grid-template-columns: repeat(2, 1fr) !important;
  }
}

@media (max-width: 640px) {
  .reviews-main-title {
    font-size: 26px !important;
  }
  .reviews-stats-card {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 16px !important;
  }
  .stat-divider {
    display: none !important;
  }
  .reviews-cards-grid {
    grid-template-columns: 1fr !important;
  }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
