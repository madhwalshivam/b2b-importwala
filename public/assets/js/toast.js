/**
 * Mudsor Central Toast & URL Query Cleaner System
 * Auto-detects ?success=..., ?error=..., ?added=1, ?updated=1, ?deleted=1 in URL,
 * displays a stackable toast, and cleans the URL via history.replaceState.
 */

window.MudsorToast = (function() {
    let container = null;

    function ensureContainer() {
        if (!container || !document.body.contains(container)) {
            container = document.createElement('div');
            container.id = 'mudsor-toast-container';
            container.className = 'fixed top-28 left-4 right-4 sm:top-5 sm:right-5 sm:left-auto z-[99999] flex flex-col space-y-2 sm:max-w-sm w-auto pointer-events-none';
            document.body.appendChild(container);
        }
        return container;
    }

    function show(message, type = 'success', duration = 1000) {
        if (!message) return;
        const parent = ensureContainer();

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto flex items-center justify-between p-3.5 px-4 rounded-2xl shadow-2xl border text-xs font-semibold transition-all duration-300 transform -translate-y-4 sm:translate-y-0 sm:translate-x-12 opacity-0 backdrop-blur-md';

        let iconSvg = '';

        if (type === 'success') {
            toast.style.borderColor = 'rgba(16, 185, 129, 0.4)';
            toast.style.backgroundColor = '#0F172A';
            toast.style.color = '#FFFFFF';
            iconSvg = `<svg class="w-4 h-4 shrink-0 text-emerald-400 mr-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;
        } else if (type === 'error' || type === 'danger') {
            toast.style.borderColor = 'rgba(239, 68, 68, 0.4)';
            toast.style.backgroundColor = '#0F172A';
            toast.style.color = '#FFFFFF';
            iconSvg = `<svg class="w-4 h-4 shrink-0 text-red-400 mr-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`;
        } else if (type === 'warning') {
            toast.style.borderColor = 'rgba(245, 158, 11, 0.4)';
            toast.style.backgroundColor = '#0F172A';
            toast.style.color = '#FFFFFF';
            iconSvg = `<svg class="w-4 h-4 shrink-0 text-amber-400 mr-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`;
        } else {
            toast.style.borderColor = 'rgba(59, 130, 246, 0.4)';
            toast.style.backgroundColor = '#0F172A';
            toast.style.color = '#FFFFFF';
            iconSvg = `<svg class="w-4 h-4 shrink-0 text-sky-400 mr-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`;
        }

        const dismissToast = () => {
            toast.classList.remove('translate-y-0', 'sm:translate-x-0', 'opacity-100');
            toast.classList.add('-translate-y-4', 'sm:translate-x-12', 'opacity-0');
            setTimeout(() => {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 300);
        };

        toast.innerHTML = `
            <div class="flex items-center space-x-1">
                ${iconSvg}
                <span class="tracking-tight">${message}</span>
            </div>
            <button type="button" class="ml-4 p-1 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition focus:outline-none shrink-0" title="Close Notification">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        `;

        const closeBtn = toast.querySelector('button');
        if (closeBtn) {
            closeBtn.addEventListener('click', dismissToast);
        }

        parent.appendChild(toast);

        // Animate In
        requestAnimationFrame(() => {
            toast.classList.remove('-translate-y-4', 'sm:translate-x-12', 'opacity-0');
            toast.classList.add('translate-y-0', 'sm:translate-x-0', 'opacity-100');
        });

        // Auto Dismiss
        if (duration > 0) {
            setTimeout(dismissToast, duration);
        }
    }

    function checkUrlQueryParams() {
        const urlParams = new URLSearchParams(window.location.search);
        let hasParam = false;

        if (urlParams.has('success')) {
            show(urlParams.get('success'), 'success');
            hasParam = true;
        } else if (urlParams.has('error')) {
            show(urlParams.get('error'), 'error');
            hasParam = true;
        } else if (urlParams.has('added')) {
            show('Item added successfully!', 'success');
            hasParam = true;
        } else if (urlParams.has('updated')) {
            show('Changes saved successfully!', 'success');
            hasParam = true;
        } else if (urlParams.has('deleted')) {
            show('Item deleted successfully!', 'success');
            hasParam = true;
        }

        // Clean URL parameters cleanly so refreshing doesn't re-trigger the toast
        if (hasParam) {
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: cleanUrl }, document.title, cleanUrl);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkUrlQueryParams);
    } else {
        checkUrlQueryParams();
    }

    return { show, checkUrlQueryParams };
})();
