<?php
/**
 * Global Image Lightbox Gallery Modal Component (Light Theme)
 * Included in main layout.php
 */
?>

<!-- Global Image Lightbox Modal -->
<div id="globalGalleryModal" class="ggm-backdrop" onclick="closeGlobalGalleryModal(event)">
  <div class="ggm-dialog" onclick="event.stopPropagation()">
    
    <!-- Modal Header -->
    <div class="ggm-header">
      <div class="ggm-header-info">
        <h4 id="ggmTitle" class="ggm-title">Product Image Gallery</h4>
        <span id="ggmCounter" class="ggm-counter">1 of 1</span>
      </div>
      <button type="button" class="ggm-close-btn" onclick="closeGlobalGalleryModal()" aria-label="Close modal">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Modal Body / Main Image Stage -->
    <div class="ggm-stage">
      <button type="button" class="ggm-nav-btn ggm-prev" onclick="moveGallerySlide(-1)" aria-label="Previous Image">
        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>

      <div class="ggm-img-wrapper">
        <img id="ggmMainImg" src="" alt="Gallery Preview" onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>';">
      </div>

      <button type="button" class="ggm-nav-btn ggm-next" onclick="moveGallerySlide(1)" aria-label="Next Image">
        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
      </button>
    </div>

    <!-- Modal Footer / Complete Thumbnail Strip -->
    <div class="ggm-thumbs-wrapper">
      <div id="ggmThumbStrip" class="ggm-thumb-strip">
        <!-- Dynamically rendered by JS -->
      </div>
    </div>

  </div>
</div>

<style>
/* Global Gallery Modal Styling - Premium Light Theme */
.ggm-backdrop {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.ggm-backdrop.active {
  opacity: 1;
  pointer-events: auto;
}

.ggm-dialog {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 24px;
  width: 100%;
  max-width: 920px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
  display: flex;
  flex-direction: column;
  max-height: 92vh;
  transform: scale(0.96);
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.ggm-backdrop.active .ggm-dialog {
  transform: scale(1);
}

.ggm-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  background: #ffffff;
  border-bottom: 1px solid #f1f5f9;
}

.ggm-header-info {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
  padding-right: 12px;
}

.ggm-title {
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-size: 15px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 620px;
}

.ggm-counter {
  font-size: 11px;
  font-weight: 700;
  color: #f05a29;
  background: #fff7ed;
  border: 1px solid #ffedd5;
  padding: 3px 12px;
  border-radius: 9999px;
  flex-shrink: 0;
  white-space: nowrap;
}

.ggm-close-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.ggm-close-btn:hover {
  background: #f05a29;
  color: #ffffff;
  border-color: #f05a29;
  transform: rotate(90deg);
}

.ggm-stage {
  position: relative;
  height: 480px;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border-bottom: 1px solid #f1f5f9;
}

.ggm-img-wrapper {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.ggm-img-wrapper img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  border-radius: 12px;
  transition: opacity 0.2s ease, transform 0.25s ease;
}

.ggm-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 10;
  width: 46px;
  height: 46px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0f172a;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.ggm-nav-btn:hover {
  background: #f05a29;
  color: #ffffff;
  border-color: #f05a29;
  transform: translateY(-50%) scale(1.08);
  box-shadow: 0 6px 18px rgba(240, 90, 41, 0.3);
}

.ggm-prev { left: 18px; }
.ggm-next { right: 18px; }

.ggm-thumbs-wrapper {
  padding: 16px 24px;
  background: #ffffff;
}

.ggm-thumb-strip {
  display: flex;
  gap: 12px;
  overflow-x: auto;
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #ffffff;
  padding-bottom: 4px;
}

.ggm-thumb-strip::-webkit-scrollbar {
  height: 4px;
}

.ggm-thumb-strip::-webkit-scrollbar-track {
  background: #ffffff;
}

.ggm-thumb-strip::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.ggm-thumb-btn {
  width: 58px;
  height: 58px;
  border-radius: 12px;
  border: 2px solid #e2e8f0;
  overflow: hidden;
  cursor: pointer;
  flex-shrink: 0;
  padding: 0;
  background: #f8fafc;
  opacity: 0.7;
  transition: all 0.2s ease;
}

.ggm-thumb-btn img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.ggm-thumb-btn:hover {
  opacity: 1;
  border-color: #cbd5e1;
}

.ggm-thumb-btn.active {
  opacity: 1;
  border-color: #f05a29;
  box-shadow: 0 0 0 3px rgba(240, 90, 41, 0.18);
  transform: scale(1.05);
}

@media (max-width: 640px) {
  .ggm-stage { height: 320px; }
  .ggm-dialog { max-height: 95vh; border-radius: 18px; }
  .ggm-title { max-width: 200px; font-size: 13px; }
  .ggm-prev { left: 8px; width: 40px; height: 40px; }
  .ggm-next { right: 8px; width: 40px; height: 40px; }
}
</style>

<script>
(function() {
  let ggmImages = [];
  let ggmCurrentIndex = 0;

  function decodeEntities(str) {
    if (!str) return '';
    const txt = document.createElement('textarea');
    txt.innerHTML = str;
    return txt.value;
  }

  window.openGlobalGalleryModal = function(images, startIndex, title) {
    if (!images || !images.length) return;
    ggmImages = images;
    ggmCurrentIndex = startIndex || 0;

    const modal = document.getElementById('globalGalleryModal');
    const titleEl = document.getElementById('ggmTitle');
    
    if (titleEl && title) {
      titleEl.textContent = decodeEntities(title);
    }
    
    updateGalleryStage();
    renderGalleryThumbnails();

    if (modal) modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  };

  window.closeGlobalGalleryModal = function(e) {
    if (e && e.target !== e.currentTarget && e.type !== 'click') return;
    const modal = document.getElementById('globalGalleryModal');
    if (modal) modal.classList.remove('active');
    document.body.style.overflow = '';
  };

  window.moveGallerySlide = function(direction) {
    if (!ggmImages.length) return;
    ggmCurrentIndex = (ggmCurrentIndex + direction + ggmImages.length) % ggmImages.length;
    updateGalleryStage();
  };

  window.setGalleryIndex = function(index) {
    if (index >= 0 && index < ggmImages.length) {
      ggmCurrentIndex = index;
      updateGalleryStage();
    }
  };

  function updateGalleryStage() {
    const mainImg = document.getElementById('ggmMainImg');
    const counter = document.getElementById('ggmCounter');

    if (mainImg) mainImg.src = ggmImages[ggmCurrentIndex] || '';
    if (counter) counter.textContent = `${ggmCurrentIndex + 1} of ${ggmImages.length}`;

    // Highlight thumbnail
    const strip = document.getElementById('ggmThumbStrip');
    if (strip) {
      const thumbs = strip.children;
      for (let i = 0; i < thumbs.length; i++) {
        if (i === ggmCurrentIndex) {
          thumbs[i].classList.add('active');
          thumbs[i].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        } else {
          thumbs[i].classList.remove('active');
        }
      }
    }

    if (typeof window.onGlobalGalleryIndexChange === 'function') {
      try { window.onGlobalGalleryIndexChange(ggmCurrentIndex, ggmImages[ggmCurrentIndex]); } catch (e) {}
    }
  }

  function renderGalleryThumbnails() {
    const strip = document.getElementById('ggmThumbStrip');
    if (!strip) return;

    strip.innerHTML = ggmImages.map((url, idx) => `
      <button type="button" 
              class="ggm-thumb-btn ${idx === ggmCurrentIndex ? 'active' : ''}" 
              onclick="setGalleryIndex(${idx})"
              title="Image ${idx + 1}">
        <img src="${url}" alt="Thumbnail ${idx + 1}" onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">
      </button>
    `).join('');
  }

  // Keyboard navigation
  document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('globalGalleryModal');
    if (!modal || !modal.classList.contains('active')) return;

    if (e.key === 'Escape') closeGlobalGalleryModal();
    if (e.key === 'ArrowLeft') moveGallerySlide(-1);
    if (e.key === 'ArrowRight') moveGallerySlide(1);
  });

  // Global helper for switching card main image
  window.switchCardMainImage = function(cardId, newSrc, btn) {
    const card = document.getElementById(cardId);
    if (!card) return;
    const mainImg = document.getElementById('mainImg_' + cardId);
    if (mainImg) mainImg.src = newSrc;

    // Highlight active thumbnail button
    const thumbs = card.querySelectorAll('.pcard-thumb-item');
    thumbs.forEach(t => t.classList.remove('active'));
    if (btn) btn.classList.add('active');
  };
})();
</script>
