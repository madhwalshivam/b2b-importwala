/**
 * Mudsor Central Logout Confirmation Modal System
 * Intercepts clicks on logout links and provides a global openLogoutModal function.
 */

window.MudsorLogout = (function () {
    let modalElement = null;
    let pendingLogoutUrl = null;

    function getFallbackLogoutUrl() {
        return window.location.pathname.includes('/admin') 
            ? '/ecommerce/admin/logout' 
            : '/ecommerce/logout';
    }

    function hideModal(e) {
        if (e) {
            if (typeof e.preventDefault === 'function') e.preventDefault();
            if (typeof e.stopPropagation === 'function') e.stopPropagation();
        }
        const modal = document.getElementById('mudsor-logout-modal');
        if (modal) {
            modal.classList.remove('opacity-100', 'pointer-events-auto');
            modal.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(() => {
                if (modal && modal.parentNode) {
                    modal.parentNode.removeChild(modal);
                }
            }, 150);
        }
        modalElement = null;
        pendingLogoutUrl = null;
        document.body.classList.remove('modal-open');
    }

    function createModal(targetUrl) {
        // Remove any previous modal instance in DOM
        const existing = document.getElementById('mudsor-logout-modal');
        if (existing && existing.parentNode) {
            existing.parentNode.removeChild(existing);
        }

        pendingLogoutUrl = targetUrl || getFallbackLogoutUrl();

        modalElement = document.createElement('div');
        modalElement.id = 'mudsor-logout-modal';
        modalElement.className = 'fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs opacity-0 pointer-events-none transition-opacity duration-200';

        modalElement.innerHTML = `
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-2xl max-w-sm w-full p-6 text-center space-y-5 transform scale-95 transition-transform duration-200" style="margin: auto;">
                <div class="w-12 h-12 rounded-full bg-red-50 dark:bg-red-950/50 text-red-600 flex items-center justify-center mx-auto border border-red-100 dark:border-red-900/50" style="background-color: rgba(168,17,28,0.08); color: #A8111C;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </div>

                <div class="space-y-1.5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-100">Log Out Confirmation</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 font-medium leading-relaxed">Are you sure you want to end your current session and log out?</p>
                </div>

                <div class="flex items-center space-x-3 pt-2">
                    <button type="button" id="mudsor-logout-cancel-btn" class="flex-1 h-11 border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-700 dark:text-slate-200 font-semibold text-xs rounded-xl transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" id="mudsor-logout-confirm-btn" class="flex-1 h-11 text-white font-semibold text-xs rounded-xl shadow-md transition cursor-pointer hover:opacity-90" style="background-color: #A8111C;">
                        Log Out
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modalElement);

        const cancelBtn = modalElement.querySelector('#mudsor-logout-cancel-btn');
        const confirmBtn = modalElement.querySelector('#mudsor-logout-confirm-btn');

        if (cancelBtn) {
            cancelBtn.addEventListener('click', hideModal);
        }

        if (confirmBtn) {
            confirmBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const dest = pendingLogoutUrl || getFallbackLogoutUrl();
                window.location.href = dest;
            });
        }

        modalElement.addEventListener('click', function (e) {
            if (e.target === modalElement) {
                hideModal(e);
            }
        });

        return modalElement;
    }

    function showModal(logoutUrl) {
        const modal = createModal(logoutUrl);
        document.body.classList.add('modal-open');
        setTimeout(() => {
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100', 'pointer-events-auto');
            const card = modal.querySelector('div');
            if (card) {
                card.classList.remove('scale-95');
                card.classList.add('scale-100');
            }
        }, 10);
    }

    function openLogoutModal(logoutUrl) {
        showModal(logoutUrl || getFallbackLogoutUrl());
    }

    function init() {
        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[href*="logout"]');
            if (link) {
                e.preventDefault();
                e.stopPropagation();
                showModal(link.href || getFallbackLogoutUrl());
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.openLogoutModal = openLogoutModal;

    return { showModal, hideModal, openLogoutModal };
})();
