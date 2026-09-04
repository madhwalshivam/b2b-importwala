<!-- Visual Search & Image Similarity Modal — ImportWala Theme -->
<div id="visualSearchModal" class="visual-search-modal-backdrop" style="display:none;"
  onclick="if(event.target===this)closeVisualSearchModal()">
  <div class="visual-search-modal-card">

    <!-- Modal Header -->
    <div class="vs-modal-header" id="vsModalHeader">
      <div style="display:flex; align-items:center; gap:12px;">
        <div
          style="width:38px; height:38px; border-radius:10px; background:#fff7ed; color:#f05a29; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <div>
          <h3
            style="font-family:'Inter', system-ui, sans-serif; font-size:16px; font-weight:600; color:#1e293b; margin:0;"
            id="vsModalTitle">Find products with Image Search</h3>
          <p style="font-size:12px; font-weight:400; color:#64748b; margin:2px 0 0 0;" id="vsModalSubtitle">Upload a photo or select a category to find matching items instantly.</p>
        </div>
      </div>
      <button type="button" onclick="closeVisualSearchModal()" class="vs-close-btn" aria-label="Close">&times;</button>
    </div>

    <!-- Drag & Drop Upload Stage -->
    <div class="vs-upload-zone" id="vsUploadZone" onclick="document.getElementById('vsFileInput').click()"
      ondragover="vsDragOver(event)" ondragleave="vsDragLeave(event)" ondrop="vsDrop(event)">
      <input type="file" id="vsFileInput" accept="image/jpeg,image/png,image/webp" style="display:none;"
        onchange="handleVisualSearchFileUpload(this)">

      <div class="vs-upload-icon-circle">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
        </svg>
      </div>

      <div style="font-size:14px; font-weight:600; color:#1e293b;">Upload or drag an image here</div>
      <p style="font-size:12px; font-weight:400; color:#64748b; margin:4px 0 0 0;">Supports JPG, PNG, WEBP &mdash; up to 10MB</p>

      <button type="button" class="vs-btn-upload-file"
        onclick="event.stopPropagation(); document.getElementById('vsFileInput').click();">
        Upload a file
      </button>
    </div>

    <!-- Searching & Analyzing Progress Loader Stage -->
    <div id="vsLoadingStage" style="display:none; text-align:center; padding:32px 16px;">
      <div class="vs-big-spinner"></div>
      <div style="font-size:16px; font-weight:600; color:#f05a29; margin-top:14px;">Searching by Image</div>
      <div style="font-size:12.5px; font-weight:400; color:#64748b; margin-top:4px;" id="vsLoadingSubtext">Analyzing visual features...</div>

      <!-- Progress Bar Track -->
      <div
        style="width:100%; max-width:380px; height:7px; background:#f1f5f9; border-radius:20px; overflow:hidden; margin:18px auto 8px auto;">
        <div id="vsProgressBar"
          style="width:0%; height:100%; background:linear-gradient(90deg, #f05a29, #ff7a45); transition:width 0.3s ease; border-radius:20px;">
        </div>
      </div>
      <div style="font-size:12px; font-weight:500; color:#64748b;" id="vsProgressPct">0% Complete</div>
    </div>

    <!-- Image Preview Stage -->
    <div id="vsPreviewStage" style="display:none; text-align:center; margin-bottom:16px;">
      <div
        style="display:inline-flex; align-items:center; gap:12px; background:#fff7ed; border:1px solid #ffedd5; padding:10px 16px; border-radius:14px; max-width:100%;">
        <div
          style="width:48px; height:48px; border-radius:10px; overflow:hidden; border:1.5px solid #f05a29; flex-shrink:0; background:#fff;">
          <img id="vsPreviewImg" src="" style="width:100%; height:100%; object-fit:cover;">
        </div>
        <div style="text-align:left;">
          <div style="font-size:12.5px; font-weight:600; color:#10b981;" id="vsStatusText">✓ Image Analyzed &mdash; Visual Matches Found!</div>
          <button type="button" onclick="resetVsUpload()"
            style="font-size:11.5px; color:#f05a29; font-weight:500; background:none; border:none; padding:0; cursor:pointer; text-decoration:underline; margin-top:2px;">
            Change / Upload Another Photo
          </button>
        </div>
      </div>
    </div>

    <!-- Tip Info Banner -->
    <div class="vs-tip-box" id="vsTipBox">
      <div class="vs-tip-icon">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <div style="font-size:12px; color:#9a3412; font-weight:400; line-height:1.4;">
        <strong>Tip:</strong> Take a photo of any product and upload it to find similar items instantly
      </div>
    </div>

    <!-- Results Section -->
    <div id="vsResultsContainer" style="margin-top:16px;">
      <!-- Dynamic matching items rendered via JS -->
    </div>

    <!-- Footer Action -->
    <div style="margin-top:20px; text-align:center; display:none;" id="vsFooterAction">
      <a id="vsViewAllBtn" href="<?= url('catalog') ?>" class="pcard-btn-action"
        style="display:inline-flex; align-items:center; justify-content:center; padding:11px 26px; text-decoration:none; font-size:13.5px; border-radius:10px; background:#f05a29; color:#fff; font-weight:600;">
        View All Visual Matches in Catalog &rarr;
      </a>
    </div>

  </div>
</div>

<style>
  .visual-search-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(5px);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
  }

  .visual-search-modal-card {
    background: #ffffff;
    border-radius: 20px;
    width: 100%;
    max-width: 560px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 24px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.2);
    position: relative;
    border: 1px solid #f1f5f9;
  }

  .vs-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 16px;
  }

  .vs-close-btn {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    font-size: 18px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    flex-shrink: 0;
  }

  .vs-close-btn:hover {
    background: #fee2e2;
    color: #ef4444;
  }

  .vs-upload-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    padding: 30px 20px;
    text-align: center;
    cursor: pointer;
    background: #f8fafc;
    transition: all 0.2s ease;
  }

  .vs-upload-zone:hover,
  .vs-upload-zone.vs-dragover {
    border-color: #f05a29;
    background: #fff7ed;
  }

  .vs-upload-icon-circle {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: #fff7ed;
    color: #f05a29;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px auto;
    transition: all 0.2s ease;
  }

  .vs-upload-zone:hover .vs-upload-icon-circle {
    background: #ffedd5;
    color: #e04f20;
  }

  .vs-btn-upload-file {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8.5px 24px;
    border-radius: 30px;
    background: #f05a29;
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    margin-top: 14px;
    transition: background 0.2s ease, transform 0.15s ease;
    box-shadow: 0 2px 8px rgba(240, 90, 41, 0.25);
  }

  .vs-btn-upload-file:hover {
    background: #e04f20;
    transform: translateY(-1px);
  }

  .vs-preset-btn {
    padding: 7px 14px;
    border-radius: 8px;
    border: 1.5px solid #e2e8f0;
    background: #ffffff;
    color: #334155;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .vs-preset-btn:hover,
  .vs-preset-btn.active {
    border-color: #f05a29;
    color: #f05a29;
    background: #fff7ed;
  }

  .vs-tip-box {
    background: #fff7ed;
    border: 1px solid #ffedd5;
    border-radius: 12px;
    padding: 11px 14px;
    margin-top: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .vs-tip-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #ffedd5;
    color: #f05a29;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  /* Spinner */
  .vs-big-spinner {
    width: 48px;
    height: 48px;
    border: 3.5px solid #ffedd5;
    border-top-color: #f05a29;
    border-radius: 50%;
    animation: vsSpin 0.7s linear infinite;
    margin: 0 auto;
  }

  .vs-spinner {
    display: inline-block;
    width: 26px;
    height: 26px;
    border: 3px solid #ffedd5;
    border-radius: 50%;
    border-top-color: #f05a29;
    animation: vsSpin 0.8s linear infinite;
  }

  @keyframes vsSpin {
    to {
      transform: rotate(360deg);
    }
  }
</style>

<script>
  (function () {
    let activeFile = null;

    window.triggerVisualSearchModal = function (productId, productName, imageSrc) {
      const modal = document.getElementById('visualSearchModal');
      if (!modal) return;
      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden';

      const title = document.getElementById('vsModalTitle');
      const subtitle = document.getElementById('vsModalSubtitle');
      const uploadZone = document.getElementById('vsUploadZone');
      const previewStage = document.getElementById('vsPreviewStage');
      const previewImg = document.getElementById('vsPreviewImg');
      const viewAllBtn = document.getElementById('vsViewAllBtn');
      const resultsContainer = document.getElementById('vsResultsContainer');
      const footerAction = document.getElementById('vsFooterAction');
      const loadingStage = document.getElementById('vsLoadingStage');
      const tipBox = document.getElementById('vsTipBox');

      resultsContainer.innerHTML = '';
      footerAction.style.display = 'none';
      loadingStage.style.display = 'none';
      tipBox.style.display = 'flex';

      if (productId) {
        title.innerText = 'Visually Similar Products';
        subtitle.innerText = `Finding items similar to: ${productName || 'Selected Item'}`;
        if (imageSrc) {
          previewImg.src = imageSrc;
          previewStage.style.display = 'block';
          uploadZone.style.display = 'none';
        }
        viewAllBtn.href = '<?= url("catalog?similar_to=") ?>' + productId;
        executeVisualSearch({ product_id: productId });
      } else {
        title.innerText = 'Find products with Image Search';
        subtitle.innerText = 'Upload a photo or select a category to find matching items instantly.';
        previewStage.style.display = 'none';
        uploadZone.style.display = 'block';
        viewAllBtn.href = '<?= url("catalog") ?>';
      }
    };

    window.closeVisualSearchModal = function () {
      const modal = document.getElementById('visualSearchModal');
      if (modal) modal.style.display = 'none';
      document.body.style.overflow = '';
    };

    window.resetVsUpload = function () {
      activeFile = null;
      document.getElementById('vsFileInput').value = '';
      document.getElementById('vsPreviewStage').style.display = 'none';
      document.getElementById('vsLoadingStage').style.display = 'none';
      document.getElementById('vsUploadZone').style.display = 'block';
      document.getElementById('vsTipBox').style.display = 'flex';
      document.getElementById('vsResultsContainer').innerHTML = '';
      document.getElementById('vsFooterAction').style.display = 'none';
    };

    window.vsDragOver = function (e) {
      e.preventDefault();
      document.getElementById('vsUploadZone').classList.add('vs-dragover');
    };
    window.vsDragLeave = function () {
      document.getElementById('vsUploadZone').classList.remove('vs-dragover');
    };
    window.vsDrop = function (e) {
      e.preventDefault();
      document.getElementById('vsUploadZone').classList.remove('vs-dragover');
      if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        processUploadedFile(e.dataTransfer.files[0]);
      }
    };

    window.handleVisualSearchFileUpload = function (input) {
      if (input.files && input.files[0]) {
        processUploadedFile(input.files[0]);
      }
    };

    function processUploadedFile(file) {
      activeFile = file;
      const reader = new FileReader();
      reader.onload = async function (e) {
        document.getElementById('vsPreviewImg').src = e.target.result;
        document.getElementById('vsUploadZone').style.display = 'none';
        document.getElementById('vsTipBox').style.display = 'none';
        document.getElementById('vsResultsContainer').innerHTML = '';
        document.getElementById('vsFooterAction').style.display = 'none';

        // 1. SHOW LOADING STATE IMMEDIATELY BEFORE BACKEND FETCH
        const loadingStage = document.getElementById('vsLoadingStage');
        const progressBar = document.getElementById('vsProgressBar');
        const progressPct = document.getElementById('vsProgressPct');
        const loadingSubtext = document.getElementById('vsLoadingSubtext');

        loadingStage.style.display = 'block';
        progressBar.style.width = '10%';
        progressPct.innerText = '10% Complete';
        loadingSubtext.innerText = 'Analyzing image features (Color, Shape, Histogram)...';

        // Simulate step progress while waiting for real async fetch
        let currentPct = 10;
        const progressInterval = setInterval(() => {
          if (currentPct < 85) {
            currentPct += 15;
            progressBar.style.width = currentPct + '%';
            progressPct.innerText = currentPct + '% Complete';
          }
        }, 100);

        const formData = new FormData();
        formData.append('photo', file);

        // 2. AWAIT REAL BACKEND API FETCH CALL
        try {
          const res = await fetch('<?= url("api/visual-search") ?>', {
            method: 'POST',
            body: formData
          });
          const data = await res.json();

          clearInterval(progressInterval);
          progressBar.style.width = '100%';

          if (data.auto_redirect && data.redirect_url) {
            loadingSubtext.innerText = '✓ Exact visual match found! Redirecting to product page...';
            progressPct.innerText = '100% Match!';
            setTimeout(() => {
              closeVisualSearchModal();
              window.location.href = data.redirect_url;
            }, 350);
            return;
          }

          loadingSubtext.innerText = 'Upload complete! Rendering results...';

          setTimeout(() => {
            loadingStage.style.display = 'none';
            document.getElementById('vsPreviewStage').style.display = 'block';
            document.getElementById('vsTipBox').style.display = 'flex';

            const items = data.items || [];
            const headline = data.headline || (data.has_matches ? '✓ Visual Matches Found' : 'No exact visual match found');
            const statusEl = document.getElementById('vsStatusText');
            if (statusEl) {
              if (data.image_parse_error) {
                statusEl.innerHTML = '<span style="color:#ef4444; font-weight:700;">⚠️ Image could not be parsed. Showing top wholesale catalog items:</span>';
              } else if (data.is_fallback) {
                statusEl.innerHTML = '<span style="color:#c2410c; font-weight:700;">No exact visual match found (&lt; 60%). Showing trending wholesale catalog items:</span>';
              } else {
                statusEl.innerHTML = '✓ Image Feature Analysis Complete &mdash; Matches Found!';
              }
            }
            renderResultsGrid(items, data.is_fallback, headline);
          }, 400);

        } catch (err) {
          clearInterval(progressInterval);
          loadingStage.style.display = 'none';
          document.getElementById('vsTipBox').style.display = 'flex';
          renderErrorUI();
        }
      };
      reader.readAsDataURL(file);
    }

    async function executeVisualSearch(paramsOrFormData, isFormData = false) {
      const container = document.getElementById('vsResultsContainer');
      const footerAction = document.getElementById('vsFooterAction');

      if (!isFormData) {
        container.innerHTML = `
        <div style="text-align:center; padding:28px 16px; background:#fff7ed; border-radius:16px;">
          <div class="vs-spinner"></div>
          <div style="font-size:13.5px; font-weight:600; color:#f05a29; margin-top:10px;">Searching by Image...</div>
          <p style="font-size:11.5px; font-weight:400; color:#64748b; margin-top:4px;">Searching catalog for top matching products...</p>
        </div>
      `;
      }

      try {
        let fetchOptions = {};
        let fetchUrl = '<?= url("api/visual-search") ?>';

        if (isFormData) {
          fetchOptions = { method: 'POST', body: paramsOrFormData };
        } else {
          const queryParams = new URLSearchParams(paramsOrFormData).toString();
          fetchUrl += '?' + queryParams;
        }

        const res = await fetch(fetchUrl, fetchOptions);
        const data = await res.json();

        if (data.auto_redirect && data.redirect_url) {
          closeVisualSearchModal();
          window.location.href = data.redirect_url;
          return;
        }

        const items = data.items || [];
        renderResultsGrid(items, data.is_fallback, data.headline);
        if (footerAction) footerAction.style.display = 'block';

      } catch (err) {
        renderErrorUI();
        if (footerAction) footerAction.style.display = 'none';
      }
    }

    function renderResultsGrid(items, isFallback = false, headline = '') {
      const container = document.getElementById('vsResultsContainer');
      const footerAction = document.getElementById('vsFooterAction');

      if (!items || items.length === 0) {
        renderNoMatchesUI();
        return;
      }

      let html = '';
      if (isFallback) {
        html += `
        <div style="margin-bottom:12px; padding:10px 14px; background:#fff7ed; border:1px solid #ffedd5; border-radius:12px; font-size:12px; color:#c2410c; font-weight:500; text-align:left;">
          ⚠️ ${escapeHtml(headline || 'No exact visual match found (< 60%). Check out our top wholesale items:')}
        </div>
        `;
      } else if (headline) {
        html += `
        <div style="margin-bottom:10px; font-size:13px; font-weight:600; color:#1e293b; text-align:left;">
          ${escapeHtml(headline)}
        </div>
        `;
      }

      html += '<div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:12px;">';
      items.forEach(item => {
        const itemUrl = '<?= url("product/") ?>' + (item.slug || item.id);
        const imgUrl = item.image_url || item.main_image || '<?= asset("assets/images/placeholder.jpg") ?>';
        const price = (parseFloat(item.price || 0)).toFixed(2);
        const matchBadge = item.match_badge || 'Visual Match';
        
        let badgeBg = '#10b981'; // Green for exact / strong match (>= 85%)
        if (item.match_type === 'related' || (item.similarity_score >= 60 && item.similarity_score < 85)) {
          badgeBg = '#f05a29'; // Orange for similar match (60-85%)
        } else if (item.is_fallback || item.similarity_score < 60) {
          badgeBg = '#64748b'; // Gray for fallback
        }

        html += `
        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; text-align:center; padding:10px; position:relative; transition:all 0.2s;" onmouseover="this.style.borderColor='#f05a29'" onmouseout="this.style.borderColor='#e2e8f0'">
          <div style="position:absolute; top:6px; right:6px; background:${badgeBg}; color:#fff; font-size:9.5px; font-weight:600; padding:2px 7px; border-radius:12px; z-index:1; box-shadow:0 2px 4px rgba(0,0,0,0.12);">
            ${escapeHtml(matchBadge)}
          </div>
          <a href="${itemUrl}" style="text-decoration:none;">
            <div style="width:100%; aspect-ratio:1/1; border-radius:10px; overflow:hidden; background:#f8fafc; margin-bottom:8px;">
              <img src="${imgUrl}" alt="${escapeHtml(item.name || '')}" style="width:100%; height:100%; object-fit:cover;">
            </div>
            <div style="font-size:12px; font-weight:600; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${escapeHtml(item.name || '')}">
              ${escapeHtml(item.name || '')}
            </div>
            <div style="font-size:13.5px; font-weight:600; color:#f05a29; margin-top:4px;">
              ₹${price}
            </div>
          </a>
        </div>
      `;
      });
      html += '</div>';
      container.innerHTML = html;
      if (footerAction) footerAction.style.display = 'block';
    }

    function renderNoMatchesUI() {
      const container = document.getElementById('vsResultsContainer');
      container.innerHTML = `
      <div style="text-align:center; padding:24px 16px; background:#fff7ed; border:1px solid #fed7aa; border-radius:16px;">
        <div style="font-size:14px; font-weight:800; color:#c2410c;">No close visual matches found</div>
        <p style="font-size:12px; color:#9a3412; margin:4px 0 0 0;">No products in catalog met the minimum similarity threshold. Try uploading a different clear product photo.</p>
      </div>
    `;
    }

    function renderErrorUI() {
      const container = document.getElementById('vsResultsContainer');
      container.innerHTML = `
      <div style="text-align:center; padding:20px; color:#ef4444; font-size:12px; font-weight:700;">
        Error performing visual search. Please check your network connection and try again.
      </div>
    `;
    }

    function escapeHtml(str) {
      return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

  })();
</script>