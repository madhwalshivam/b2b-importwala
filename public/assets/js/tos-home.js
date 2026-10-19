(function () {
  function openMenu() {
    var overlay = document.getElementById('tosMenuOverlay');
    if (!overlay) return;
    var src = document.getElementById('headerCartCount');
    var dst = document.getElementById('tosOverlayCartCount');
    if (src && dst) dst.textContent = (src.textContent || '0').trim();
    overlay.hidden = false;
    requestAnimationFrame(function () { overlay.classList.add('is-open'); });
    document.body.classList.add('tos-menu-lock');
  }

  function closeMenu() {
    var overlay = document.getElementById('tosMenuOverlay');
    if (!overlay) return;
    overlay.classList.remove('is-open');
    document.body.classList.remove('tos-menu-lock');
    setTimeout(function () { if (!overlay.classList.contains('is-open')) overlay.hidden = true; }, 220);
  }

  window.tosOpenMenu = openMenu;
  window.tosCloseMenu = closeMenu;

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeMenu();
  });

  var ticking = false;
  function onScroll() {
    if (!document.body.classList.contains('tos-home')) return;
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(function () {
      document.body.classList.toggle('is-stuck', window.scrollY > 48);
      ticking = false;
    });
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  function bindCarousel(root) {
    if (!root) return;
    var track = root.querySelector('[data-tos-track]');
    var slides = track ? Array.prototype.slice.call(track.children) : [];
    if (!slides.length) return;
    var index = 0;
    var timer = null;
    var discover = document.getElementById('tosDiscover');

    function show(i) {
      index = (i + slides.length) % slides.length;
      slides.forEach(function (slide, n) {
        slide.classList.toggle('is-active', n === index);
      });
      if (discover && slides[index].dataset.href) discover.setAttribute('href', slides[index].dataset.href);
    }

    function next() { show(index + 1); }
    function prev() { show(index - 1); }

    function start() {
      stop();
      timer = setInterval(next, 5000);
    }
    function stop() { if (timer) clearInterval(timer); }

    var prevBtn = root.querySelector('[data-tos-prev]');
    var nextBtn = root.querySelector('[data-tos-next]');
    if (prevBtn) prevBtn.addEventListener('click', function () { prev(); start(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { next(); start(); });

    var startX = 0;
    track.addEventListener('touchstart', function (e) {
      startX = e.changedTouches[0].screenX;
      stop();
    }, { passive: true });
    track.addEventListener('touchend', function (e) {
      var dx = e.changedTouches[0].screenX - startX;
      if (dx < -40) next();
      else if (dx > 40) prev();
      start();
    }, { passive: true });

    show(0);
    start();
  }

  function bindStrip(root) {
    if (!root) return;
    var track = root.querySelector('[data-tos-strip]');
    if (!track) return;
    function step(dir) {
      var tile = track.querySelector('.tos-prod-tile');
      var width = tile ? tile.getBoundingClientRect().width : track.clientWidth * 0.18;
      track.scrollBy({ left: dir * width, behavior: 'smooth' });
    }
    var prevBtn = root.querySelector('[data-tos-strip-prev]');
    var nextBtn = root.querySelector('[data-tos-strip-next]');
    if (prevBtn) prevBtn.addEventListener('click', function () { step(-1); });
    if (nextBtn) nextBtn.addEventListener('click', function () { step(1); });
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindCarousel(document.getElementById('tosYellow'));
    bindStrip(document.getElementById('tosProducts'));
  });
})();
