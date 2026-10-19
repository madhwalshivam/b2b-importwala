(function () {
  var placeholder = window.IW_CARD_PLACEHOLDER || '';

  function markLoaded(img) {
    if (img && img.classList) img.classList.add('is-loaded');
  }

  function hydrate(root) {
    var scope = root && root.querySelectorAll ? root : document;
    var nodes = [];
    if (scope.matches && scope.matches('img.iw-card-img')) nodes.push(scope);
    if (scope.querySelectorAll) {
      scope.querySelectorAll('img.iw-card-img').forEach(function (img) { nodes.push(img); });
    }
    nodes.forEach(function (img) {
      if (img.classList.contains('is-loaded')) return;
      if (!img.getAttribute('src')) return;
      if (img.complete) markLoaded(img);
    });
  }

  function promote(wrap) {
    if (!wrap || wrap.getAttribute('data-iw-promoted') === '1') return;
    var pending = wrap.querySelectorAll('img[data-src]');
    if (!pending.length) {
      wrap.setAttribute('data-iw-promoted', '1');
      return;
    }
    wrap.setAttribute('data-iw-promoted', '1');
    pending.forEach(function (img) {
      var url = img.getAttribute('data-src');
      if (!url || img.getAttribute('src')) return;
      img.loading = 'eager';
      img.decoding = 'async';
      img.src = url;
      img.removeAttribute('data-src');
    });
  }

  function wrapFrom(node) {
    if (!node || !node.closest) return null;
    return node.closest('.ef-swipe-wrap');
  }

  document.addEventListener('pointerover', function (e) {
    var wrap = wrapFrom(e.target);
    if (wrap) promote(wrap);
  }, true);

  document.addEventListener('pointerdown', function (e) {
    var wrap = wrapFrom(e.target);
    if (wrap) promote(wrap);
  }, true);

  document.addEventListener('touchstart', function (e) {
    var wrap = wrapFrom(e.target);
    if (wrap) promote(wrap);
  }, { capture: true, passive: true });

  document.addEventListener('scroll', function (e) {
    var track = e.target;
    if (!track || !track.classList || !track.classList.contains('ef-swipe-track')) return;
    var wrap = track.closest ? track.closest('.ef-swipe-wrap') : null;
    if (wrap) promote(wrap);
    if (!wrap || !track.offsetWidth) return;
    var dots = wrap.querySelectorAll('.ef-swipe-dot');
    if (!dots.length) return;
    var idx = Math.round(track.scrollLeft / track.offsetWidth);
    dots.forEach(function (dot, i) { dot.classList.toggle('active', i === idx); });
  }, true);

  document.addEventListener('load', function (e) {
    var img = e.target;
    if (img && img.classList && img.classList.contains('iw-card-img')) markLoaded(img);
  }, true);

  document.addEventListener('error', function (e) {
    var img = e.target;
    if (!img || !img.classList || !img.classList.contains('iw-card-img')) return;
    if (!img.getAttribute('src')) return;
    if (img.getAttribute('data-iw-err') === '1') {
      markLoaded(img);
      return;
    }
    img.setAttribute('data-iw-err', '1');
    if (placeholder && img.src.indexOf(placeholder) === -1) {
      img.src = placeholder;
    } else {
      markLoaded(img);
    }
  }, true);

  window.iwHydrateCardImages = hydrate;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { hydrate(document); });
  } else {
    hydrate(document);
  }

  if (window.MutationObserver && document.documentElement) {
    var observer = new MutationObserver(function (records) {
      records.forEach(function (record) {
        record.addedNodes.forEach(function (node) {
          if (!node || node.nodeType !== 1) return;
          if ((node.matches && node.matches('img.iw-card-img')) || (node.querySelector && node.querySelector('img.iw-card-img'))) {
            hydrate(node);
          }
        });
      });
    });
    observer.observe(document.documentElement, { childList: true, subtree: true });
  }
})();
