<?php
/**
 * Global Image Lightbox Gallery Modal Component
 * Included in main layout.php
 */
?>

<!-- Global Image Lightbox Modal -->
<div id="globalGalleryModal" class="ggm-backdrop" onclick="closeGlobalGalleryModal(event)">
  <div class="ggm-dialog" onclick="event.stopPropagation()">
    
    <!-- Modal Header -->
    <div class="ggm-header">
      <div>
        <h4 id="ggmTitle" class="ggm-title">Product Image Gallery</h4>
        <span id="ggmCounter" class="ggm-counter">Image 1 of 1</span>
      </div>
      <button type="button" class="ggm-close-btn" onclick="closeGlobalGalleryModal()" aria-label="Close modal">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <!-- Modal Body / Main Image Stage -->
    <div class="ggm-stage">
      <button type="button" class="ggm-nav-btn ggm-prev" onclick="moveGallerySlide(-1)" aria-label="Previous Image">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
      </button>

      <div class="ggm-img-wrapper">
        <img id="ggmMainImg" src="" alt="Gallery Preview" onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>';">
      </div>

      <button type="button" class="ggm-nav-btn ggm-next" onclick="moveGallerySlide(1)" aria-label="Next Image">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
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
/* Global Gallery Modal Styling */
.ggm-backdrop {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background: rgba(15, 23, 42, 0.85);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.25s ease;
}

.ggm-backdrop.active {
  opacity: 1;
  pointer-events: auto;
}

.ggm-dialog {
  background: #ffffff;
  border-radius: 24px;
  width: 100%;
  max-width: 860px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  animation: ggmPop 0.25s ease-out;
}

@keyframes ggmPop {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.ggm-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  border-bottom: 1px solid #f1f5f9;
}

.ggm-title {
  font-family: 'Inter', system-ui, sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.ggm-counter {
  font-size: 12px;
  color: #64748b;
  font-weight: 500;
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
}

.ggm-close-btn:hover {
  background: #0f172a;
  color: #ffffff;
  border-color: #0f172a;
}

.ggm-stage {
  position: relative;
  height: 420px;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.ggm-img-wrapper {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.ggm-img-wrapper img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  border-radius: 12px;
  transition: transform 0.2s ease;
}

.ggm-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 10;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0f172a;
  transition: all 0.2s ease;
}

.ggm-nav-btn:hover {
  background: #f05a29;
  color: #ffffff;
  border-color: #f05a29;
}

.ggm-prev { left: 16px; }
.ggm-next { right: 16px; }

.ggm-thumbs-wrapper {
  padding: 16px 24px;
  background: #ffffff;
  border-top: 1px solid #f1f5f9;
}

.ggm-thumb-strip {
  display: flex;
  gap: 10px;
  overflow-x: auto;
  scrollbar-width: thin;
  padding-bottom: 4px;
}

.ggm-thumb-strip::-webkit-scrollbar {
  height: 4px;
}

.ggm-thumb-btn {
  width: 56px;
  height: 56px;
  border-radius: 10px;
  border: 2px solid #e2e8f0;
  overflow: hidden;
  cursor: pointer;
  flex-shrink: 0;
  padding: 0;
  background: #f8fafc;
  transition: all 0.2s ease;
}

.ggm-thumb-btn img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.ggm-thumb-btn.active {
  border-color: #f05a29;
  box-shadow: 0 0 0 2px rgba(240, 90, 41, 0.2);
}

@media (max-width: 640px) {
  .ggm-stage { height: 300px; }
  .ggm-dialog { max-height: 95vh; border-radius: 16px; }
}
</style>

<script>
(function() {
  let ggmImages = [];
  let ggmCurrentIndex = 0;

  window.openGlobalGalleryModal = function(images, startIndex, title) {
    if (!images || !images.length) return;
    ggmImages = images;
    ggmCurrentIndex = startIndex || 0;

    const modal = document.getElementById('globalGalleryModal');
    const titleEl = document.getElementById('ggmTitle');
    
    if (titleEl && title) titleEl.textContent = title;
    
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
    if (counter) counter.textContent = `Image ${ggmCurrentIndex + 1} of ${ggmImages.length}`;

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
