<?php
/**
 * Everful-Style Testimonials / Customer Reviews Homepage Partial
 * Clean Inter typography, Indian customer reviews, NO initial letter circles, NO green color.
 * @var array $testimonials
 */
if (empty($testimonials)) return;
?>

<div class="everful-testimonials-wrapper">
  <div class="everful-testimonials-container">

    <!-- Left Column: Tagline & Trust Badge (Vertically Centered) -->
    <div class="everful-testimonials-left">
      <h2 class="everful-testimonials-title">
        Effortless sourcing <br class="hidden-mobile">the ImportWale way
      </h2>
      
      <div class="everful-trust-badge">
        <span class="everful-trust-label">Source:</span>
        <span class="everful-trust-icon">★</span>
        <span class="everful-trust-text">Verified Buyer Reviews</span>
      </div>

      <div class="everful-testimonials-cta-desktop">
        <a href="<?= url('reviews') ?>" class="everful-view-all-btn">
          <span>View All Reviews</span>
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:15px !important; height:15px !important; display:inline-block !important;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
          </svg>
        </a>
      </div>
    </div>

    <!-- Right Column: 2x3 Responsive Review Cards Grid -->
    <div class="everful-testimonials-right">
      <div class="everful-cards-grid">
        <?php foreach ($testimonials as $item): ?>
          <div class="everful-review-card">
            
            <!-- Rating Row (5 Amber Star Rating Boxes - NO GREEN) -->
            <div class="everful-rating-row">
              <?php for ($s = 1; $s <= 5; $s++): ?>
                <span class="everful-star-box <?= $s <= $item['rating'] ? 'active' : '' ?>">
                  <svg viewBox="0 0 24 24" width="13" height="13" fill="#FFFFFF" class="no-size-reset">
                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                  </svg>
                </span>
              <?php endfor; ?>
            </div>

            <!-- Review Text Snippet -->
            <p class="everful-review-text" title="<?= htmlspecialchars($item['review_text']) ?>">
              "<?= htmlspecialchars($item['review_text']) ?>"
            </p>

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

      <div class="everful-testimonials-cta-mobile">
        <a href="<?= url('reviews') ?>" class="everful-view-all-btn">
          <span>View All Reviews</span>
          <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:15px !important; height:15px !important;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
          </svg>
        </a>
      </div>
    </div>

  </div>
</div>

<style>
/* Everful Section Layout Base */
.everful-testimonials-wrapper {
  background: #F8FAFC !important;
  border: 1px solid #E2E8F0 !important;
  border-radius: 20px !important;
  padding: 32px 24px !important;
  margin-bottom: 24px !important;
  box-sizing: border-box !important;
  font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
}

.everful-testimonials-container {
  display: flex !important;
  gap: 40px !important;
  align-items: center !important; /* Vertically centered heading relative to right grid! */
  max-width: 1320px !important;
  margin: 0 auto !important;
}

/* Left Column */
.everful-testimonials-left {
  flex: 0 0 300px !important;
  max-width: 300px !important;
  padding-top: 0 !important; /* Clean vertical alignment */
}

.everful-testimonials-title {
  font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
  font-size: 30px !important;
  font-weight: 600 !important;
  line-height: 1.25 !important;
  color: #0F172A !important;
  margin: 0 0 16px 0 !important;
  letter-spacing: -0.01em !important;
}

.everful-trust-badge {
  display: flex !important;
  align-items: center !important;
  gap: 8px !important;
  font-size: 14.5px !important;
  font-weight: 600 !important;
  color: #1E293B !important;
}

.everful-trust-label {
  color: #64748B !important;
  font-weight: 500 !important;
}

.everful-trust-icon {
  color: #f05a29 !important; /* Brand accent orange! */
  font-size: 18px !important;
}

.everful-trust-text {
  color: #0F172A !important;
  font-weight: 700 !important;
}

.everful-testimonials-cta-desktop {
  margin-top: 32px !important;
}

.everful-testimonials-cta-mobile {
  display: none !important;
  margin-top: 24px !important;
  text-align: center !important;
}

.everful-view-all-btn {
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  padding: 12px 24px !important;
  background: #f05a29 !important;
  color: #ffffff !important;
  text-decoration: none !important;
  border-radius: 99px !important;
  font-size: 13.5px !important;
  font-weight: 700 !important;
  transition: all 0.22s ease !important;
  box-shadow: none !important;
}

.everful-view-all-btn:hover {
  background: #d8481b !important;
  transform: translateY(-2px) !important;
}

/* Right Column Grid */
.everful-testimonials-right {
  flex: 1 1 0% !important;
  width: 100% !important;
  min-width: 0 !important;
}

.everful-cards-grid {
  display: grid !important;
  grid-template-columns: repeat(3, 1fr) !important;
  gap: 20px !important;
}

/* Review Card */
.everful-review-card {
  background: #FFFFFF !important;
  border-radius: 16px !important;
  padding: 24px !important;
  display: flex !important;
  flex-direction: column !important;
  justify-content: space-between !important;
  min-height: 210px !important;
  box-shadow: none !important;
  transition: all 0.22s ease !important;
  border: 1px solid #E2E8F0 !important;
  box-sizing: border-box !important;
}

.everful-review-card:hover {
  transform: translateY(-3px) !important;
  box-shadow: none !important;
  border-color: #CBD5E1 !important;
}

/* Star Box Row - Brand Orange #f05a29 */
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
  background: #E2E8F0 !important;
  color: #FFFFFF !important;
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  border-radius: 4px !important;
  flex-shrink: 0 !important;
}

.everful-star-box.active {
  background: #f05a29 !important; /* Theme Brand Orange */
}

.everful-star-box svg {
  width: 13px !important;
  height: 13px !important;
  fill: #FFFFFF !important;
  display: block !important;
}

/* Review Text */
.everful-review-text {
  font-size: 13.5px !important;
  line-height: 1.55 !important;
  color: #334155 !important;
  margin: 0 0 20px 0 !important;
  display: -webkit-box !important;
  -webkit-line-clamp: 4 !important;
  -webkit-box-orient: vertical !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  flex-grow: 1 !important;
  font-weight: 400 !important;
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

/* Responsive Media Queries */
@media (max-width: 1080px) {
  .everful-testimonials-container {
    flex-direction: column !important;
    gap: 24px !important;
    align-items: flex-start !important;
  }
  .everful-testimonials-left {
    flex: none !important;
    max-width: 100% !important;
    width: 100% !important;
    padding-top: 0 !important;
  }
  .everful-testimonials-title {
    font-size: 26px !important;
  }
  .everful-testimonials-cta-desktop {
    display: none !important;
  }
  .everful-testimonials-cta-mobile {
    display: block !important;
  }
  .everful-cards-grid {
    grid-template-columns: repeat(2, 1fr) !important;
  }
}

@media (max-width: 640px) {
  .everful-testimonials-wrapper {
    padding: 28px 18px !important;
    border-radius: 16px !important;
    margin-bottom: 32px !important;
  }
  .everful-testimonials-title {
    font-size: 22px !important;
  }
  .everful-cards-grid {
    grid-template-columns: 1fr !important;
    gap: 16px !important;
  }
  .everful-review-card {
    padding: 20px !important;
    min-height: auto !important;
  }
  .hidden-mobile {
    display: none !important;
  }
}
</style>
