<!-- Visual Search Modal — upload step only (results open on /image-search) -->
<div id="visualSearchModal" class="visual-search-modal-backdrop" style="display:none;"
  onclick="if(event.target===this)closeVisualSearchModal()">
  <div class="visual-search-modal-card">

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
          <p style="font-size:12px; font-weight:400; color:#64748b; margin:2px 0 0 0;" id="vsModalSubtitle">Upload a photo to find matching wholesale items.</p>
        </div>
      </div>
      <button type="button" onclick="closeVisualSearchModal()" class="vs-close-btn" aria-label="Close">&times;</button>
    </div>

    <div class="vs-upload-zone" id="vsUploadZone" onclick="document.getElementById('vsFileInput').click()"
      ondragover="vsDragOver(event)" ondragleave="vsDragLeave(event)" ondrop="vsDrop(event)">
      <input type="file" id="vsFileInput" accept="image/*" capture="environment" style="display:none;"
        onchange="handleVisualSearchFileUpload(this)">

      <div class="vs-upload-icon-circle">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
        </svg>
      </div>

      <div style="font-size:14px; font-weight:600; color:#1e293b;">Upload or drag an image here</div>
      <p style="font-size:12px; font-weight:400; color:#64748b; margin:4px 0 0 0;">Camera or gallery &mdash; JPG, PNG, WEBP, GIF</p>

      <button type="button" class="vs-btn-upload-file"
        onclick="event.stopPropagation(); document.getElementById('vsFileInput').click();">
        Upload a file
      </button>
    </div>

    <div id="vsLoadingStage" style="display:none; text-align:center; padding:32px 16px;">
      <div class="vs-big-spinner"></div>
      <div style="font-size:16px; font-weight:600; color:#f05a29; margin-top:14px;">Analyzing image...</div>
      <div style="font-size:12.5px; font-weight:400; color:#64748b; margin-top:4px;" id="vsLoadingSubtext">Finding visually similar products</div>
      <div
        style="width:100%; max-width:380px; height:7px; background:#f1f5f9; border-radius:20px; overflow:hidden; margin:18px auto 8px auto;">
        <div id="vsProgressBar"
          style="width:0%; height:100%; background:linear-gradient(90deg, #f05a29, #ff7a45); transition:width 0.3s ease; border-radius:20px;">
        </div>
      </div>
      <div style="font-size:12px; font-weight:500; color:#64748b;" id="vsProgressPct">0% Complete</div>
    </div>

    <div id="vsErrorBox" style="display:none; margin-top:14px; padding:12px 14px; background:#fef2f2; border:1px solid #fecaca; border-radius:12px; color:#b91c1c; font-size:12.5px; font-weight:600;"></div>
    <div id="vsProductIdResults" style="display:none; margin-top:14px;"></div>

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

  </div>
</div>

<style>
  .visual-search-modal-backdrop {
    position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(5px);
    z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 16px;
  }
  .visual-search-modal-card {
    background: #fff; border-radius: 20px; width: 100%; max-width: 520px; max-height: 90vh;
    overflow-y: auto; padding: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.2);
    position: relative; border: 1px solid #f1f5f9;
  }
  .vs-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding-bottom: 14px; border-bottom: 1px solid #f1f5f9; margin-bottom: 16px;
  }
  .vs-close-btn {
    width: 30px; height: 30px; border-radius: 50%; border: none; background: #f1f5f9; color: #64748b;
    font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center;
  }
  .vs-close-btn:hover { background: #fee2e2; color: #ef4444; }
  .vs-upload-zone {
    border: 2px dashed #cbd5e1; border-radius: 16px; padding: 30px 20px; text-align: center;
    cursor: pointer; background: #f8fafc; transition: all 0.2s ease;
  }
  .vs-upload-zone:hover, .vs-upload-zone.vs-dragover { border-color: #f05a29; background: #fff7ed; }
  .vs-upload-icon-circle {
    width: 46px; height: 46px; border-radius: 50%; background: #fff7ed; color: #f05a29;
    display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;
  }
  .vs-btn-upload-file {
    display: inline-flex; align-items: center; justify-content: center; padding: 8.5px 24px;
    border-radius: 30px; background: #f05a29; color: #fff; font-size: 13px; font-weight: 600;
    border: none; cursor: pointer; margin-top: 14px; box-shadow: 0 2px 8px rgba(240,90,41,0.25);
  }
  .vs-btn-upload-file:hover { background: #e04f20; }
  .vs-tip-box {
    background: #fff7ed; border: 1px solid #ffedd5; border-radius: 12px; padding: 11px 14px;
    margin-top: 16px; display: flex; align-items: center; gap: 10px;
  }
  .vs-tip-icon {
    width: 24px; height: 24px; border-radius: 50%; background: #ffedd5; color: #f05a29;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
  }
  .vs-big-spinner {
    width: 48px; height: 48px; border: 3.5px solid #ffedd5; border-top-color: #f05a29;
    border-radius: 50%; animation: vsSpin 0.7s linear infinite; margin: 0 auto;
  }
  @keyframes vsSpin { to { transform: rotate(360deg); } }
</style>

<script>
  (function () {
    window.triggerVisualSearchModal = function (productId, productName, imageSrc) {
      const modal = document.getElementById('visualSearchModal');
      if (!modal) return;
      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
      resetVsUpload(true);

      // Product-id "similar" shortcut: still supported via API (no results page token)
      if (productId) {
        document.getElementById('vsModalTitle').innerText = 'Visually Similar Products';
        document.getElementById('vsModalSubtitle').innerText = 'Finding items similar to: ' + (productName || 'Selected Item');
        document.getElementById('vsUploadZone').style.display = 'none';
        document.getElementById('vsLoadingStage').style.display = 'block';
        document.getElementById('vsLoadingSubtext').innerText = 'Searching catalog...';
        fetch('<?= url("api/visual-search") ?>?product_id=' + encodeURIComponent(productId) + '&limit=12')
          .then(function (r) { return r.json(); })
          .then(function (data) {
            document.getElementById('vsLoadingStage').style.display = 'none';
            var items = data.items || [];
            var box = document.getElementById('vsProductIdResults');
            if (!items.length) {
              showVsError('No similar products found for this item.');
              document.getElementById('vsUploadZone').style.display = 'block';
              return;
            }
            var html = '<div style="font-size:13px;font-weight:700;margin-bottom:10px;color:#1e293b;">Similar products</div>';
            html += '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">';
            items.slice(0, 9).forEach(function (item) {
              var u = item.product_url || ('<?= url("product/") ?>' + (item.slug || item.id));
              var img = item.image_url || item.main_image || '';
              var price = (parseFloat(item.price || item.sale_price || 0)).toFixed(2);
              html += '<a href="' + u + '" style="text-decoration:none;border:1px solid #e2e8f0;border-radius:12px;padding:8px;text-align:center;">';
              html += '<div style="aspect-ratio:1;border-radius:8px;overflow:hidden;background:#f8fafc;margin-bottom:6px;"><img src="' + img + '" style="width:100%;height:100%;object-fit:cover;" loading="lazy"></div>';
              html += '<div style="font-size:11px;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' + (item.name || '') + '</div>';
              html += '<div style="font-size:12px;font-weight:700;color:#f05a29;margin-top:3px;">₹' + price + '</div></a>';
            });
            html += '</div>';
            box.innerHTML = html;
            box.style.display = 'block';
            document.getElementById('vsTipBox').style.display = 'flex';
          })
          .catch(function () {
            document.getElementById('vsLoadingStage').style.display = 'none';
            document.getElementById('vsUploadZone').style.display = 'block';
            showVsError('Could not load similar products. Please try again.');
          });
      }
    };

    window.closeVisualSearchModal = function () {
      const modal = document.getElementById('visualSearchModal');
      if (modal) modal.style.display = 'none';
      document.body.style.overflow = '';
    };

    window.resetVsUpload = function (keepOpen) {
      var input = document.getElementById('vsFileInput');
      if (input) input.value = '';
      document.getElementById('vsLoadingStage').style.display = 'none';
      document.getElementById('vsUploadZone').style.display = 'block';
      document.getElementById('vsTipBox').style.display = 'flex';
      var pidBox = document.getElementById('vsProductIdResults');
      if (pidBox) { pidBox.style.display = 'none'; pidBox.innerHTML = ''; }
      hideVsError();
      document.getElementById('vsModalTitle').innerText = 'Find products with Image Search';
      document.getElementById('vsModalSubtitle').innerText = 'Upload a photo to find matching wholesale items.';
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

    function showVsError(msg) {
      var box = document.getElementById('vsErrorBox');
      if (!box) return;
      box.style.display = 'block';
      box.textContent = msg;
    }
    function hideVsError() {
      var box = document.getElementById('vsErrorBox');
      if (box) { box.style.display = 'none'; box.textContent = ''; }
    }

    function resizeImageForUpload(file) {
      return new Promise(function (resolve) {
        if (!file || !file.type || !file.type.startsWith('image/')) {
          resolve(file);
          return;
        }
        var img = new Image();
        var url = URL.createObjectURL(file);
        img.onload = function () {
          try {
            var maxSide = 1000;
            var w = img.naturalWidth || img.width;
            var h = img.naturalHeight || img.height;
            if (w < 1 || h < 1) { URL.revokeObjectURL(url); resolve(file); return; }
            var scale = Math.min(1, maxSide / Math.max(w, h));
            w = Math.max(1, Math.round(w * scale));
            h = Math.max(1, Math.round(h * scale));
            var canvas = document.createElement('canvas');
            canvas.width = w; canvas.height = h;
            var ctx = canvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, w, h);
            ctx.drawImage(img, 0, 0, w, h);
            canvas.toBlob(function (blob) {
              URL.revokeObjectURL(url);
              if (!blob) { resolve(file); return; }
              var outName = (file.name || 'photo').replace(/\.[^.]+$/, '') + '.jpg';
              resolve(new File([blob], outName, { type: 'image/jpeg', lastModified: Date.now() }));
            }, 'image/jpeg', 0.85);
          } catch (err) {
            URL.revokeObjectURL(url);
            resolve(file);
          }
        };
        img.onerror = function () { URL.revokeObjectURL(url); resolve(file); };
        img.src = url;
      });
    }

    async function processUploadedFile(file) {
      hideVsError();
      var loadingStage = document.getElementById('vsLoadingStage');
      var progressBar = document.getElementById('vsProgressBar');
      var progressPct = document.getElementById('vsProgressPct');
      var loadingSubtext = document.getElementById('vsLoadingSubtext');

      document.getElementById('vsUploadZone').style.display = 'none';
      document.getElementById('vsTipBox').style.display = 'none';
      loadingStage.style.display = 'block';
      progressBar.style.width = '10%';
      progressPct.innerText = '10% Complete';
      loadingSubtext.innerText = 'Preparing photo...';

      var resized = await resizeImageForUpload(file);

      progressBar.style.width = '25%';
      progressPct.innerText = '25% Complete';
      loadingSubtext.innerText = 'Analyzing image...';

      var currentPct = 25;
      var progressInterval = setInterval(function () {
        if (currentPct < 85) {
          currentPct += 8;
          progressBar.style.width = currentPct + '%';
          progressPct.innerText = currentPct + '% Complete';
        }
      }, 140);

      var formData = new FormData();
      formData.append('photo', resized, resized.name || 'photo.jpg');
      formData.append('limit', '96');

      var abortCtrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
      var abortTimer = setTimeout(function () {
        if (abortCtrl) abortCtrl.abort();
      }, 45000);

      try {
        var res = await fetch('<?= url("api/visual-search") ?>', {
          method: 'POST',
          body: formData,
          signal: abortCtrl ? abortCtrl.signal : undefined
        });
        var raw = await res.text();
        var data = {};
        try { data = raw ? JSON.parse(raw) : {}; } catch (parseErr) {
          throw new Error('Server returned a non-JSON response (' + res.status + ').');
        }
        clearTimeout(abortTimer);
        clearInterval(progressInterval);
        progressBar.style.width = '100%';
        progressPct.innerText = '100% Complete';

        if (!res.ok || data.image_parse_error || data.success === false) {
          loadingStage.style.display = 'none';
          document.getElementById('vsUploadZone').style.display = 'block';
          document.getElementById('vsTipBox').style.display = 'flex';
          showVsError(data.message || 'Could not process this image. Try JPG/PNG under 10MB.');
          return;
        }

        var dest = data.results_url || data.redirect_url;
        if (dest) {
          loadingSubtext.innerText = 'Opening results...';
          closeVisualSearchModal();
          window.location.href = dest;
          return;
        }

        loadingStage.style.display = 'none';
        document.getElementById('vsUploadZone').style.display = 'block';
        document.getElementById('vsTipBox').style.display = 'flex';
        showVsError('Search completed but no results page was created. Please try again.');
      } catch (err) {
        clearTimeout(abortTimer);
        clearInterval(progressInterval);
        loadingStage.style.display = 'none';
        document.getElementById('vsUploadZone').style.display = 'block';
        document.getElementById('vsTipBox').style.display = 'flex';
        var aborted = err && (err.name === 'AbortError' || /aborted/i.test(String(err.message || '')));
        showVsError(aborted
          ? 'Search is taking too long. The image index may be missing on the server — try again or contact support.'
          : (err && err.message ? err.message : 'Network error. Please check your connection and try again.'));
      }
    }
  })();
</script>
