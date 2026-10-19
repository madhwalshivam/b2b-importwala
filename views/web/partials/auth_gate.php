<?php
$iwLoggedIn = !empty($_SESSION['user_id']);
$iwProfileDone = $iwLoggedIn && (int)($_SESSION['profile_completed'] ?? 0) === 1;
$iwGuestState = ['atLimit' => false, 'viewedIds' => [], 'limit' => 3];
try {
    $iwGuestState = \App\Services\GuestProductViewService::frontState();
} catch (\Throwable $e) {
}
$iwShowGuestPopup = !empty($_SESSION['guest_login_popup']);
if ($iwShowGuestPopup) {
    unset($_SESSION['guest_login_popup']);
}

$iwSkipTimed = $iwLoggedIn;
$iwPath = strtolower((string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '/'));
$iwRewrite = strtolower(trim((string)($_GET['url'] ?? ''), '/'));
$iwBases = array_filter([
    rtrim((string)parse_url((string)env('APP_URL', ''), PHP_URL_PATH) ?: '', '/'),
    rtrim((string)env('APP_BASE_PATH', ''), '/'),
]);
foreach ($iwBases as $iwBase) {
    $iwBase = strtolower($iwBase);
    if ($iwBase !== '' && str_starts_with($iwPath, $iwBase)) {
        $iwPath = substr($iwPath, strlen($iwBase)) ?: '/';
        break;
    }
}
$iwPath = '/' . ltrim($iwPath, '/');
if (!$iwSkipTimed && preg_match('#(^|/)(login|signup|forgot-password|reset-password|complete-profile|checkout|admin)(/|$)#', $iwPath . '/' . $iwRewrite)) {
    $iwSkipTimed = true;
}
?>
<div id="iw-auth-modal" style="display:none; position:fixed; inset:0; background:rgba(17,24,39,.55); z-index:10000100; align-items:center; justify-content:center; padding:16px; overflow-y:auto; -webkit-overflow-scrolling:touch;">
  <div id="iw-auth-dialog" style="background:#fff; max-width:420px; width:100%; border-radius:16px; padding:24px; position:relative; margin:auto; max-height:min(90vh, 640px); overflow:auto;">
    <button type="button" id="iw-auth-close" aria-label="Close" title="Close"
      style="position:absolute; right:12px; top:12px; width:36px; height:36px; border:1px solid #e5e7eb; background:#f3f4f6; border-radius:999px; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#111827; z-index:2;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
        <path d="M6 6l12 12M18 6L6 18"/>
      </svg>
    </button>
    <h2 id="iw-auth-title" style="font-size:20px; font-weight:700; margin:0 40px 6px 0;">Login to view more products</h2>
    <p id="iw-auth-sub" style="font-size:13px; color:#6b7280; margin-bottom:14px;">Login to view more products and for a better experience.</p>
    <p id="iw-auth-error" style="display:none; color:#dc2626; font-size:13px; margin-bottom:10px;"></p>
    <form id="iw-auth-form" method="POST" action="<?= url('login') ?>" style="display:flex; flex-direction:column; gap:10px;">
      <?= csrf_field() ?>
      <input type="hidden" name="return" id="iw-auth-return" value="account">
      <input type="email" name="email" required placeholder="Email" autocomplete="username" style="height:42px; border:1px solid #d1d5db; border-radius:10px; padding:0 12px;">
      <input type="password" name="password" required placeholder="Password" autocomplete="current-password" style="height:42px; border:1px solid #d1d5db; border-radius:10px; padding:0 12px;">
      <button type="submit" style="height:42px; background:#f05a29; color:#fff; border:none; border-radius:10px; font-weight:600;">Login</button>
      <a id="iw-auth-signup" href="<?= url('signup') ?>" style="height:42px; display:flex; align-items:center; justify-content:center; border:1px solid #d1d5db; border-radius:10px; font-weight:600; text-decoration:none; color:#111827;">Sign Up</a>
    </form>
  </div>
</div>
<script>
window.IW_AUTH = {
  loggedIn: <?= $iwLoggedIn ? 'true' : 'false' ?>,
  profileComplete: <?= $iwProfileDone ? 'true' : 'false' ?>,
  loginUrl: <?= json_encode(url('login')) ?>,
  signupUrl: <?= json_encode(url('signup')) ?>,
  completeUrl: <?= json_encode(url('complete-profile')) ?>,
  csrf: <?= json_encode(csrf_token()) ?>,
  skipTimed: <?= $iwSkipTimed ? 'true' : 'false' ?>
};
window.IW_GUEST = <?= json_encode($iwGuestState) ?>;
window.iwActiveReturn = window.iwActiveReturn || '';
window.iwAuthKind = null;
window.iwTimedTimer = null;
window.iwScrollY = 0;

function iwGuestLimit() {
  var n = parseInt((window.IW_GUEST && window.IW_GUEST.limit) || 3, 10);
  if (!n || n >= 20 || n === 5) return 3;
  return n;
}
function iwReadViewed() {
  var ids = [];
  function add(n) {
    n = parseInt(n, 10);
    if (n > 0 && ids.indexOf(n) === -1) ids.push(n);
  }
  if (window.IW_GUEST && Array.isArray(window.IW_GUEST.viewedIds)) {
    window.IW_GUEST.viewedIds.forEach(add);
  }
  var m = document.cookie.match(/(?:^|; )iw_gv=([^;]*)/);
  if (m) {
    decodeURIComponent(m[1]).split(',').forEach(add);
  }
  try {
    var ls = JSON.parse(localStorage.getItem('iw_gv') || '[]');
    if (Array.isArray(ls)) ls.forEach(add);
  } catch (err) {}
  return ids;
}
function iwWriteViewed(ids) {
  var out = [];
  (ids || []).forEach(function (n) {
    n = parseInt(n, 10);
    if (n > 0 && out.indexOf(n) === -1) out.push(n);
  });
  if (window.IW_GUEST) {
    window.IW_GUEST.viewedIds = out;
    window.IW_GUEST.atLimit = out.length >= iwGuestLimit();
  }
  document.cookie = 'iw_gv=' + out.join(',') + ';path=/;max-age=31536000;SameSite=Lax';
  try { localStorage.setItem('iw_gv', JSON.stringify(out)); } catch (err) {}
  return out;
}
function iwNoteProduct(pid) {
  pid = parseInt(pid || '0', 10);
  if (!pid) return;
  var ids = iwReadViewed();
  if (ids.indexOf(pid) === -1) {
    ids.push(pid);
    iwWriteViewed(ids);
  }
}
function iwBlockProductNav(pid) {
  if (window.IW_AUTH && window.IW_AUTH.loggedIn) return false;
  pid = parseInt(pid || '0', 10);
  var ids = iwReadViewed();
  if (pid && ids.indexOf(pid) !== -1) return false;
  return ids.length >= iwGuestLimit();
}
iwWriteViewed(iwReadViewed());
window.addEventListener('pageshow', function () {
  iwWriteViewed(iwReadViewed());
});

function iwReturnPath() {
  if (window.iwActiveReturn) return window.iwActiveReturn;
  var path = location.pathname + location.search;
  var base = <?= json_encode(rtrim((string)parse_url(env('APP_URL', ''), PHP_URL_PATH) ?: '', '/')) ?>;
  if (base && path.indexOf(base) === 0) path = path.slice(base.length);
  return path.replace(/^\//, '') || 'account';
}

function iwLockScroll() {
  if (document.body.getAttribute('data-iw-locked') === '1') return;
  document.body.setAttribute('data-iw-locked', '1');
  window.iwScrollY = window.scrollY || window.pageYOffset || 0;
  document.documentElement.style.overflow = 'hidden';
  document.body.style.overflow = 'hidden';
  document.body.style.position = 'fixed';
  document.body.style.top = '-' + window.iwScrollY + 'px';
  document.body.style.left = '0';
  document.body.style.right = '0';
  document.body.style.width = '100%';
}
function iwUnlockScroll() {
  if (document.body.getAttribute('data-iw-locked') !== '1') return;
  document.body.removeAttribute('data-iw-locked');
  document.documentElement.style.overflow = '';
  document.body.style.overflow = '';
  document.body.style.position = '';
  document.body.style.top = '';
  document.body.style.left = '';
  document.body.style.right = '';
  document.body.style.width = '';
  window.scrollTo(0, window.iwScrollY || 0);
}

function iwModalOpen() {
  var modal = document.getElementById('iw-auth-modal');
  return !!(modal && modal.style.display === 'flex');
}

function iwTimedCount() {
  try {
    var n = parseInt(sessionStorage.getItem('iw_timed_auth') || '0', 10);
    return n > 0 ? n : 0;
  } catch (err) {
    return 0;
  }
}
function iwTimedBump() {
  try {
    sessionStorage.setItem('iw_timed_auth', String(iwTimedCount() + 1));
  } catch (err) {}
}
function iwSkipTimedPath() {
  if (window.IW_AUTH && window.IW_AUTH.loggedIn) return true;
  if (window.IW_AUTH && window.IW_AUTH.skipTimed) return true;
  var p = (location.pathname + ' ' + location.search).toLowerCase();
  return /\/login(\/|$|\?)|\/signup(\/|$|\?)|\/forgot-password|\/reset-password|\/complete-profile|\/checkout|\/admin(\/|$|\?)/.test(p);
}
function iwScheduleTimedPopup() {
  if (iwSkipTimedPath()) return;
  if (iwTimedCount() >= 3) return;
  if (window.iwTimedTimer) clearTimeout(window.iwTimedTimer);
  window.iwTimedTimer = setTimeout(function () {
    if (iwSkipTimedPath()) return;
    if (iwTimedCount() >= 3) return;
    if (iwModalOpen()) return;
    iwTimedBump();
    iwOpenAuthModal(iwReturnPath(), 'timed');
  }, 15000);
}

function iwOpenAuthModal(returnPath, kind) {
  kind = kind || 'gate';
  if (kind !== 'product' && window.iwAuthKind === 'product' && iwModalOpen()) {
    return;
  }
  window.iwAuthKind = kind;
  window.iwActiveReturn = returnPath || iwReturnPath();
  var ret = document.getElementById('iw-auth-return');
  var signup = document.getElementById('iw-auth-signup');
  var title = document.getElementById('iw-auth-title');
  var sub = document.getElementById('iw-auth-sub');
  var closeBtn = document.getElementById('iw-auth-close');
  var err = document.getElementById('iw-auth-error');
  if (ret) ret.value = window.iwActiveReturn;
  if (signup) signup.href = window.IW_AUTH.signupUrl + '?return=' + encodeURIComponent(window.iwActiveReturn);
  if (kind === 'product' || kind === 'timed') {
    if (title) title.textContent = 'Login to view more products';
    if (sub) sub.textContent = 'Login to view more products and for a better experience.';
  } else {
    if (title) title.textContent = 'Login to continue';
    if (sub) sub.textContent = 'Quotes, inquiries and orders need an account with a mobile number.';
  }
  if (closeBtn) closeBtn.style.display = kind === 'product' ? 'none' : 'flex';
  if (err) { err.style.display = 'none'; err.textContent = ''; }
  var modal = document.getElementById('iw-auth-modal');
  if (modal) modal.style.display = 'flex';
  iwLockScroll();
}

function iwCloseAuthModal() {
  if (window.iwAuthKind === 'product') return;
  var modal = document.getElementById('iw-auth-modal');
  if (modal) modal.style.display = 'none';
  window.iwAuthKind = null;
  iwUnlockScroll();
  iwScheduleTimedPopup();
}
document.getElementById('iw-auth-close')?.addEventListener('click', function (e) {
  e.preventDefault();
  e.stopPropagation();
  iwCloseAuthModal();
});
document.getElementById('iw-auth-modal')?.addEventListener('click', function (e) {
  if (e.target === this) iwCloseAuthModal();
});
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') iwCloseAuthModal();
});

function iwAuthError(message) {
  var box = document.getElementById('iw-auth-error');
  if (box) { box.style.display = 'block'; box.textContent = message; }
}

function iwIsProductHref(href) {
  if (!href || href.charAt(0) === '#') return false;
  try {
    var u = new URL(href, location.origin);
    if (/\/product\//.test(u.pathname)) return true;
    if (/[?&]url=product\//.test(u.search)) return true;
  } catch (err) {}
  return /product\//.test(href);
}

document.addEventListener('click', function (e) {
  if (e.target.closest('#iw-auth-modal')) return;
  if (e.target.closest('.ef-wishlist-btn, .wishlist-btn, .ef-cart-btn')) return;

  var link = e.target.closest('a[href]');
  var href = link ? (link.getAttribute('href') || '') : '';
  var pid = 0;
  if (link && link.getAttribute('data-product-id')) {
    pid = parseInt(link.getAttribute('data-product-id'), 10);
  }
  if (!pid) {
    var card = e.target.closest('[data-product-id]');
    if (card) pid = parseInt(card.getAttribute('data-product-id') || '0', 10);
  }

  if (link && iwIsProductHref(href)) {
    if (iwBlockProductNav(pid)) {
      e.preventDefault();
      e.stopPropagation();
      iwOpenAuthModal(iwReturnPath(), 'product');
      return;
    }
    if (pid) iwNoteProduct(pid);
  }

  if (!link) return;
  var needsLogin = href.indexOf('/checkout') !== -1 || /\/inquiry(\?|$)/.test(href);
  if (!needsLogin) return;
  if (!window.IW_AUTH.loggedIn) {
    e.preventDefault();
    iwOpenAuthModal(href.replace(/^https?:\/\/[^/]+/, '').replace(/^\//, ''));
  } else if (!window.IW_AUTH.profileComplete) {
    e.preventDefault();
    window.location.href = window.IW_AUTH.completeUrl + '?return=' + encodeURIComponent(iwReturnPath());
  }
}, true);

<?php if (!empty($iwShowGuestPopup)): ?>
iwOpenAuthModal(iwReturnPath(), 'product');
<?php endif; ?>

if (typeof openRfqModal === 'function') {
  var iwOrigRfq = openRfqModal;
  window.openRfqModal = function () {
    if (!window.IW_AUTH.loggedIn) {
      iwOpenAuthModal(iwReturnPath());
      return;
    }
    if (!window.IW_AUTH.profileComplete) {
      window.location.href = window.IW_AUTH.completeUrl + '?return=' + encodeURIComponent(iwReturnPath());
      return;
    }
    return iwOrigRfq.apply(this, arguments);
  };
}
if (typeof openRfqWithProducts === 'function') {
  var iwOrigRfqProducts = openRfqWithProducts;
  window.openRfqWithProducts = function () {
    if (!window.IW_AUTH.loggedIn) {
      iwOpenAuthModal(iwReturnPath());
      return;
    }
    if (!window.IW_AUTH.profileComplete) {
      window.location.href = window.IW_AUTH.completeUrl + '?return=' + encodeURIComponent(iwReturnPath());
      return;
    }
    return iwOrigRfqProducts.apply(this, arguments);
  };
}

if (!window.iwFetchWrapped) {
  window.iwFetchWrapped = true;
  var iwOrigFetch = window.fetch;
  window.fetch = function () {
    return iwOrigFetch.apply(this, arguments).then(function (res) {
      if (res.status === 401 || res.status === 403) {
        res.clone().json().then(function (data) {
          if (!data) return;
          if (data.code === 'login_required' || data.code === 'guest_view_limit') {
            iwOpenAuthModal(data.return || iwReturnPath(), data.code === 'guest_view_limit' ? 'product' : 'gate');
          } else if (data.code === 'profile_incomplete' && data.redirect) {
            window.location.href = data.redirect;
          }
        }).catch(function () {});
      }
      return res;
    });
  };
}

document.getElementById('iw-auth-form')?.addEventListener('submit', function (e) {
  e.preventDefault();
  var form = e.target;
  fetch(form.action, {
    method: 'POST',
    body: new FormData(form),
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': window.IW_AUTH.csrf }
  }).then(function (r) { return r.json(); }).then(function (data) {
    if (data.success && data.redirect) window.location.href = data.redirect;
    else iwAuthError(data.message || 'Login failed.');
  }).catch(function () { form.submit(); });
});

iwScheduleTimedPopup();
</script>
