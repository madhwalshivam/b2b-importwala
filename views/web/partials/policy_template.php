<?php
/**
 * Unified Stripe/Notion-Style Policy & Legal Page Template Partial
 * Reusable across all policy pages.
 * 
 * @var string $pageTitle
 * @var string $badgeText
 * @var string $badgeIcon
 * @var string $lastUpdated
 * @var string $currentSlug
 * @var array  $sections
 * @var bool   $showBusinessAddress
 */

$pageTitle = $pageTitle ?? 'Legal Policy';
$badgeText = $badgeText ?? 'Official Policy';
$badgeIcon = $badgeIcon ?? 'doc';
$lastUpdated = $lastUpdated ?? 'January 15, 2026';
$currentSlug = $currentSlug ?? '';
$sections = $sections ?? [];
$showBusinessAddress = $showBusinessAddress ?? true;

// Icon SVG helper map
$svgIcons = [
    'truck' => '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><rect x="1" y="3" width="15" height="13" rx="2"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>',
    'refresh' => '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>',
    'doc' => '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
    'shield' => '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>',
    'cancel' => '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    'credit-card' => '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>',
];

$activeIconSvg = $svgIcons[$badgeIcon] ?? $svgIcons['doc'];

// Cross-links for footer
$allPolicies = [
    [
        'slug' => 'shipping-policy',
        'title' => 'Shipping & Delivery',
        'icon' => 'truck',
        'badge' => 'Logistics'
    ],
    [
        'slug' => 'refund-policy',
        'title' => 'Return & Refund Policy',
        'icon' => 'refresh',
        'badge' => 'Claims'
    ],
    [
        'slug' => 'terms-and-conditions',
        'title' => 'Terms & Conditions',
        'icon' => 'doc',
        'badge' => 'Legal Terms'
    ],
    [
        'slug' => 'privacy-policy',
        'title' => 'Privacy Policy',
        'icon' => 'shield',
        'badge' => 'Data Privacy'
    ],
    [
        'slug' => 'cancellation-policy',
        'title' => 'Cancellation Policy',
        'icon' => 'cancel',
        'badge' => 'Orders'
    ],
    [
        'slug' => 'payment-policy',
        'title' => 'Payment Policy',
        'icon' => 'credit-card',
        'badge' => 'Payments'
    ],
];
?>

<div class="policy-wrapper">
  
  <!-- Enriched Header Banner -->
  <div class="policy-header-card">
    <div class="policy-header-top">
      <div class="policy-badge-pill">
        <span class="policy-badge-icon"><?= $activeIconSvg ?></span>
        <span><?= htmlspecialchars($badgeText) ?></span>
      </div>
    </div>

    <h1 class="policy-main-heading"><?= htmlspecialchars($pageTitle) ?></h1>

    <div class="policy-meta-row">
      <span class="policy-domain-tag">www.importwale.com</span>
      <span class="policy-meta-dot">•</span>
      <span class="policy-updated-text">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" style="display:inline-block; vertical-align:-1px; margin-right:4px;">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 16 14"></polyline>
        </svg>
        Last Updated: <?= htmlspecialchars($lastUpdated) ?>
      </span>
    </div>
  </div>

  <!-- Mobile Collapsible Table of Contents Select Dropdown -->
  <?php if (!empty($sections)): ?>
    <div class="policy-mobile-toc">
      <label for="policyMobileTocSelect" class="policy-mobile-toc-label">Jump to Section:</label>
      <select id="policyMobileTocSelect" onchange="if(this.value){ document.querySelector(this.value)?.scrollIntoView({behavior:'smooth'}); }" class="policy-mobile-toc-select">
        <option value="">-- Choose a Section --</option>
        <?php foreach ($sections as $index => $sec): ?>
          <option value="#<?= htmlspecialchars($sec['id']) ?>">
            <?= htmlspecialchars($sec['number'] ?? ($index + 1)) ?>. <?= htmlspecialchars($sec['title']) ?>
          </option>
        <?php endforeach; ?>
        <?php if ($showBusinessAddress): ?>
          <option value="#business-address">Corporate Address</option>
        <?php endif; ?>
      </select>
    </div>
  <?php endif; ?>

  <!-- 2-Column Main Layout: Continuous Content + Sticky TOC Sidebar -->
  <div class="policy-body-layout">
    
    <!-- Left Column: Single Continuous White Card (Notion/Stripe Style) -->
    <div class="policy-content-column">
      <div class="policy-continuous-card">
        
        <?php foreach ($sections as $index => $sec): 
          $secId = $sec['id'] ?? ('section-' . ($index + 1));
          $secNum = $sec['number'] ?? sprintf('%02d', $index + 1);
        ?>
          <section id="<?= htmlspecialchars($secId) ?>" class="policy-section-block">
            <div class="policy-section-header">
              <span class="policy-num-circle"><?= htmlspecialchars($secNum) ?></span>
              <h2 class="policy-section-title"><?= htmlspecialchars($sec['title']) ?></h2>
            </div>
            <div class="policy-section-body">
              <?php if (is_array($sec['content'])): ?>
                <?php foreach ($sec['content'] as $paragraph): ?>
                  <p><?= $paragraph ?></p>
                <?php endforeach; ?>
              <?php else: ?>
                <?= $sec['content'] ?>
              <?php endif; ?>
            </div>
          </section>
        <?php endforeach; ?>

        <!-- Highlighted Corporate Business Address Box (Last Section) -->
        <?php if ($showBusinessAddress): ?>
          <section id="business-address" class="policy-section-block policy-address-box">
            <div class="policy-address-icon-wrap">
              <svg width="24" height="24" fill="none" stroke="#f05a29" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <div class="policy-address-content">
              <h3 class="policy-address-heading">Registered Corporate Address &amp; Legal Entity</h3>
              <p class="policy-address-company">Importwale Wholesale Inc.</p>
              <p class="policy-address-text">
                476 A1, Niti Khand-2, Indirapuram, Ghaziabad, Uttar Pradesh 201014, India
              </p>
              <p class="policy-address-support">
                Official Support: <a href="mailto:support@importwale.com" style="color:#f05a29; font-weight:600; text-decoration:none;">support@importwale.com</a>
              </p>
            </div>
          </section>
        <?php endif; ?>

      </div><!-- /policy-continuous-card -->

      <!-- Related Legal Policies Cross-Links Block -->
      <div class="policy-related-block">
        <h3 class="policy-related-title">
          <svg width="18" height="18" fill="none" stroke="#f05a29" viewBox="0 0 24 24" stroke-width="2" style="display:inline-block; vertical-align:-3px; margin-right:6px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
          </svg>
          Related Legal &amp; Policy Documents
        </h3>
        
        <div class="policy-related-grid">
          <?php foreach ($allPolicies as $pol):
            $isCurrent = ($pol['slug'] === $currentSlug);
            if ($isCurrent) continue;
            $iconSvg = $svgIcons[$pol['icon']] ?? $svgIcons['doc'];
          ?>
            <a href="<?= url($pol['slug']) ?>" class="policy-related-card">
              <div class="policy-related-card-header">
                <span class="policy-related-icon"><?= $iconSvg ?></span>
                <span class="policy-related-badge"><?= htmlspecialchars($pol['badge']) ?></span>
              </div>
              <div class="policy-related-card-title"><?= htmlspecialchars($pol['title']) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

    </div><!-- /policy-content-column -->

    <!-- Right Column: Sticky Table of Contents (TOC) Sidebar -->
    <?php if (!empty($sections)): ?>
      <div class="policy-sidebar-column">
        <div class="policy-toc-card">
          <div class="policy-toc-header">
            <svg width="16" height="16" fill="none" stroke="#f05a29" viewBox="0 0 24 24" stroke-width="2.2">
              <line x1="8" y1="6" x2="21" y2="6"></line>
              <line x1="8" y1="12" x2="21" y2="12"></line>
              <line x1="8" y1="18" x2="21" y2="18"></line>
              <line x1="3" y1="6" x2="3.01" y2="6"></line>
              <line x1="3" y1="12" x2="3.01" y2="12"></line>
              <line x1="3" y1="18" x2="3.01" y2="18"></line>
            </svg>
            <span>Table of Contents</span>
          </div>

          <nav class="policy-toc-nav" id="policyTocNav">
            <?php foreach ($sections as $index => $sec): 
              $secId = $sec['id'] ?? ('section-' . ($index + 1));
              $secNum = $sec['number'] ?? ($index + 1);
            ?>
              <a href="#<?= htmlspecialchars($secId) ?>" class="policy-toc-link" data-section="<?= htmlspecialchars($secId) ?>">
                <span class="policy-toc-num"><?= htmlspecialchars($secNum) ?>.</span>
                <span class="policy-toc-text"><?= htmlspecialchars($sec['title']) ?></span>
              </a>
            <?php endforeach; ?>

            <?php if ($showBusinessAddress): ?>
              <a href="#business-address" class="policy-toc-link" data-section="business-address">
                <span class="policy-toc-num">★</span>
                <span class="policy-toc-text">Corporate Address</span>
              </a>
            <?php endif; ?>
          </nav>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<style>
/* Policy Wrapper Layout Base */
.policy-wrapper {
  max-width: 1200px;
  margin: 28px auto 60px auto !important;
  font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
  color: #1E293B;
}

/* Enriched Header Card */
.policy-header-card {
  background: #F8FAFC !important;
  border: 1px solid #E2E8F0 !important;
  border-radius: 20px !important;
  padding: 32px 36px !important;
  margin-bottom: 28px !important;
}

.policy-header-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}

.policy-badge-pill {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: #FFF5F2 !important;
  color: #f05a29 !important;
  border: 1px solid #FDE8E0 !important;
  padding: 5px 14px;
  border-radius: 99px;
  font-size: 12.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.policy-badge-icon {
  display: inline-flex;
  align-items: center;
  color: #f05a29;
}

.policy-print-btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: #FFFFFF !important;
  border: 1px solid #CBD5E1 !important;
  color: #475569 !important;
  padding: 7px 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.policy-print-btn:hover {
  background: #0F172A !important;
  color: #FFFFFF !important;
  border-color: #0F172A !important;
}

.policy-main-heading {
  font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
  font-size: 30px !important;
  font-weight: 600 !important;
  color: #0F172A !important;
  margin: 0 0 10px 0 !important;
  line-height: 1.25 !important;
  letter-spacing: -0.01em !important;
}

.policy-meta-row {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13.5px;
  color: #64748B;
  font-weight: 500;
}

.policy-domain-tag {
  color: #f05a29;
  font-weight: 600;
}

.policy-meta-dot {
  color: #94A3B8;
}

/* Mobile TOC Dropdown */
.policy-mobile-toc {
  display: none;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 12px 16px;
  margin-bottom: 20px;
}

.policy-mobile-toc-label {
  font-size: 13px;
  font-weight: 700;
  color: #0F172A;
  margin-right: 8px;
}

.policy-mobile-toc-select {
  width: 100%;
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #CBD5E1;
  font-size: 13.5px;
  color: #0F172A;
  background: #FFFFFF;
}

/* 2-Column Body Layout */
.policy-body-layout {
  display: flex;
  gap: 32px;
  align-items: flex-start;
}

.policy-content-column {
  flex: 1 1 0%;
  min-width: 0;
  max-width: 840px;
}

/* Single Continuous White Card Container */
.policy-continuous-card {
  background: #FFFFFF !important;
  border: 1px solid #E2E8F0 !important;
  border-radius: 20px !important;
  padding: 40px 36px !important;
  box-sizing: border-box !important;
  box-shadow: none !important;
}

/* Section Block Styling */
.policy-section-block {
  margin-bottom: 36px;
  padding-bottom: 32px;
  border-bottom: 1px solid #F1F5F9;
  scroll-margin-top: 100px;
}

.policy-section-block:last-of-type {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.policy-section-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.policy-num-circle {
  width: 28px;
  height: 28px;
  min-width: 28px;
  background: #FFF5F2;
  color: #f05a29;
  border: 1px solid #FDE8E0;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12.5px;
  font-weight: 700;
  flex-shrink: 0;
}

.policy-section-title {
  font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
  font-size: 19px !important;
  font-weight: 600 !important;
  color: #0F172A !important;
  margin: 0 !important;
  line-height: 1.3 !important;
}

.policy-section-body {
  font-size: 15px;
  color: #334155;
  line-height: 1.75;
  padding-left: 40px;
}

.policy-section-body p {
  margin: 0 0 14px 0;
}

.policy-section-body p:last-child {
  margin-bottom: 0;
}

.policy-section-body ul, .policy-section-body ol {
  margin: 0 0 14px 0;
  padding-left: 20px;
}

.policy-section-body li {
  margin-bottom: 6px;
}

/* Highlighted Corporate Address Box */
.policy-address-box {
  background: #F8FAFC !important;
  border: 1px solid #E2E8F0 !important;
  border-radius: 16px !important;
  padding: 24px !important;
  display: flex !important;
  gap: 16px !important;
  align-items: flex-start !important;
  margin-top: 36px !important;
}

.policy-address-icon-wrap {
  width: 44px;
  height: 44px;
  min-width: 44px;
  background: #FFF5F2;
  border: 1px solid #FDE8E0;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.policy-address-content {
  flex: 1;
}

.policy-address-heading {
  font-size: 16px;
  font-weight: 700;
  color: #0F172A;
  margin: 0 0 4px 0;
}

.policy-address-company {
  font-size: 14.5px;
  font-weight: 700;
  color: #f05a29;
  margin: 0 0 4px 0;
}

.policy-address-text {
  font-size: 14px;
  color: #475569;
  margin: 0 0 6px 0;
  line-height: 1.5;
}

.policy-address-support {
  font-size: 13.5px;
  color: #64748B;
  margin: 0;
}

/* Related Policies Cross-Links Footer */
.policy-related-block {
  margin-top: 36px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 20px;
  padding: 28px 24px;
}

.policy-related-title {
  font-size: 16px;
  font-weight: 700;
  color: #0F172A;
  margin: 0 0 18px 0;
  display: flex;
  align-items: center;
}

.policy-related-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
}

.policy-related-card {
  background: #FFFFFF;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 14px 16px;
  text-decoration: none;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.policy-related-card:hover {
  border-color: #f05a29;
  transform: translateY(-2px);
}

.policy-related-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.policy-related-icon {
  color: #f05a29;
  display: inline-flex;
}

.policy-related-badge {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748B;
  background: #F1F5F9;
  padding: 2px 8px;
  border-radius: 4px;
}

.policy-related-card-title {
  font-size: 13.5px;
  font-weight: 600;
  color: #0F172A;
  line-height: 1.35;
}

/* Sticky TOC Sidebar */
.policy-sidebar-column {
  flex: 0 0 280px;
  width: 280px;
  position: sticky;
  top: 95px;
}

.policy-toc-card {
  background: #FFFFFF !important;
  border: 1px solid #E2E8F0 !important;
  border-radius: 18px !important;
  padding: 22px 20px !important;
  box-shadow: none !important;
}

.policy-toc-header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14.5px;
  font-weight: 700;
  color: #0F172A;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #F1F5F9;
}

.policy-toc-nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.policy-toc-link {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 7px 10px;
  border-radius: 8px;
  text-decoration: none;
  font-size: 13.5px;
  color: #64748B;
  font-weight: 500;
  line-height: 1.4;
  transition: all 0.18s ease;
  border-left: 2px solid transparent;
}

.policy-toc-link:hover {
  color: #f05a29;
  background: #FFF5F2;
}

.policy-toc-link.active {
  color: #f05a29 !important;
  font-weight: 700 !important;
  background: #FFF5F2 !important;
  border-left-color: #f05a29 !important;
}

.policy-toc-num {
  color: #94A3B8;
  font-weight: 600;
  font-size: 12px;
  width: 18px;
  min-width: 18px;
}

.policy-toc-link.active .policy-toc-num {
  color: #f05a29;
}

.policy-toc-text {
  flex: 1;
}

/* Responsive Media Queries */
@media (max-width: 992px) {
  .policy-body-layout {
    flex-direction: column;
  }
  .policy-sidebar-column {
    display: none;
  }
  .policy-mobile-toc {
    display: block;
  }
  .policy-content-column {
    max-width: 100%;
  }
  .policy-related-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .policy-continuous-card {
    padding: 24px 18px !important;
  }
  .policy-section-body {
    padding-left: 0;
    margin-top: 10px;
  }
  .policy-related-grid {
    grid-template-columns: 1fr;
  }
  .policy-header-card {
    padding: 24px 20px !important;
  }
  .policy-main-heading {
    font-size: 24px !important;
  }
}

/* Print Styles */
@media print {
  body {
    background: #FFFFFF !important;
    color: #000000 !important;
  }
  header, footer, .policy-sidebar-column, .policy-print-btn, .policy-related-block, .policy-mobile-toc {
    display: none !important;
  }
  .policy-wrapper {
    max-width: 100% !important;
    margin: 0 !important;
  }
  .policy-continuous-card {
    border: none !important;
    padding: 0 !important;
  }
}
</style>

<!-- Scroll-Spy JavaScript for Table of Contents -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const sections = document.querySelectorAll('.policy-section-block');
  const navLinks = document.querySelectorAll('.policy-toc-link');

  if (!sections.length || !navLinks.length) return;

  function onScrollSpy() {
    let currentId = '';
    const scrollPos = window.scrollY + 140;

    sections.forEach(function(section) {
      const top = section.offsetTop;
      const height = section.offsetHeight;
      if (scrollPos >= top && scrollPos < (top + height)) {
        currentId = section.getAttribute('id');
      }
    });

    if (!currentId && sections.length > 0) {
      if (window.scrollY < sections[0].offsetTop) {
        currentId = sections[0].getAttribute('id');
      }
    }

    navLinks.forEach(function(link) {
      link.classList.remove('active');
      if (link.getAttribute('data-section') === currentId) {
        link.classList.add('active');
      }
    });
  }

  window.addEventListener('scroll', onScrollSpy);
  onScrollSpy(); // Initial trigger
});
</script>
