</main>
</div>

<!-- GLOBAL CUSTOM ADMIN CONFIRMATION MODAL & TOAST UI SYSTEM -->
<div id="admin-confirm-modal"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-xs transition-opacity duration-200 hidden">
    <div id="admin-confirm-modal-box"
        class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6 space-y-5 transform transition-all duration-200 scale-95">
        <div class="flex items-start space-x-4">
            <div
                class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900/50 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-6 h-6 text-red-600 dark:text-red-400"></i>
            </div>
            <div class="space-y-1 min-w-0 flex-1">
                <h3 id="admin-confirm-title" class="text-base font-semibold text-gray-900 dark:text-white">Confirm
                    Action</h3>
                <p id="admin-confirm-message" class="text-xs text-gray-600 dark:text-slate-400 leading-relaxed">Are you
                    sure you want to proceed?</p>
            </div>
        </div>

        <div id="admin-confirm-preview"
            class="hidden justify-center p-2 bg-gray-50 dark:bg-slate-800/60 rounded-xl border border-gray-200 dark:border-slate-700">
            <img id="admin-confirm-preview-img" src="" class="max-h-32 rounded-lg object-contain">
        </div>

        <div class="flex items-center justify-end space-x-3 pt-2">
            <button type="button" id="admin-confirm-cancel-btn"
                class="px-4 py-2 bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-700 text-gray-700 dark:text-slate-300 font-semibold text-xs rounded-xl border border-gray-300 dark:border-slate-700 transition cursor-pointer">
                Cancel
            </button>
            <button type="button" id="admin-confirm-action-btn"
                class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-md flex items-center space-x-1.5 cursor-pointer">
                <span id="admin-confirm-action-text">Delete</span>
            </button>
        </div>
    </div>
</div>

<!-- GLOBAL TOAST NOTIFICATION CONTAINER -->
<div id="admin-toast-container" class="fixed bottom-5 right-5 z-[99999] flex flex-col space-y-2 pointer-events-none">
</div>

<script>
    lucide.createIcons();

    // GLOBAL CUSTOM UI TOAST NOTIFICATION
    window.showToast = function (message, type = 'success') {
        const container = document.getElementById('admin-toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        const isSuccess = type === 'success';

        toast.className = `pointer-events-auto flex items-center space-x-3 px-4 py-3 rounded-xl border shadow-xl transition-all duration-300 transform translate-y-4 opacity-0 text-xs font-semibold ${isSuccess
                ? 'bg-slate-900 dark:bg-slate-800 text-white border-emerald-500/50 shadow-emerald-950/20'
                : 'bg-red-900 text-white border-red-500/50 shadow-red-950/20'
            }`;

        toast.innerHTML = `
                <i data-lucide="${isSuccess ? 'check-circle' : 'alert-circle'}" class="w-4 h-4 ${isSuccess ? 'text-emerald-400' : 'text-red-400'} shrink-0"></i>
                <span>${message}</span>
            `;

        container.appendChild(toast);
        lucide.createIcons({ props: {}, nameAttr: 'data-lucide', elements: [toast] });

        setTimeout(() => {
            toast.classList.remove('translate-y-4', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        }, 10);

        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-4', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

    // GLOBAL CUSTOM CONFIRM MODAL
    window.showConfirmModal = function ({
        title = 'Confirm Action',
        message = 'Are you sure you want to proceed?',
        previewImg = null,
        confirmText = 'Delete',
        onConfirm = () => { }
    }) {
        const modal = document.getElementById('admin-confirm-modal');
        const modalBox = document.getElementById('admin-confirm-modal-box');
        const titleEl = document.getElementById('admin-confirm-title');
        const msgEl = document.getElementById('admin-confirm-message');
        const previewWrap = document.getElementById('admin-confirm-preview');
        const previewImgEl = document.getElementById('admin-confirm-preview-img');
        const cancelBtn = document.getElementById('admin-confirm-cancel-btn');
        const actionBtn = document.getElementById('admin-confirm-action-btn');
        const actionText = document.getElementById('admin-confirm-action-text');

        if (!modal) return;

        titleEl.textContent = title;
        msgEl.textContent = message;
        actionText.textContent = confirmText;

        if (previewImg) {
            previewImgEl.src = previewImg;
            previewWrap.classList.remove('hidden');
            previewWrap.classList.add('flex');
        } else {
            previewWrap.classList.add('hidden');
            previewWrap.classList.remove('flex');
        }

        modal.classList.remove('hidden');
        lucide.createIcons();

        setTimeout(() => {
            modalBox.classList.remove('scale-95');
            modalBox.classList.add('scale-100');
        }, 10);

        const closeModal = () => {
            modalBox.classList.remove('scale-100');
            modalBox.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 150);
        };

        cancelBtn.onclick = closeModal;
        modal.onclick = (e) => {
            if (e.target === modal) closeModal();
        };

        actionBtn.onclick = async () => {
            actionBtn.disabled = true;
            actionText.textContent = 'Processing...';
            try {
                await onConfirm();
            } catch (err) {
                console.error(err);
            } finally {
                actionBtn.disabled = false;
                actionText.textContent = confirmText;
                closeModal();
            }
        };
    };

    // INTERCEPT NATIVE BROWSER CONFIRMS ON LINKS & BUTTONS AUTOMATICALLY
    document.addEventListener('click', function (e) {
        const target = e.target.closest('a, button, [onclick]');
        if (!target) return;
        if (target.dataset.confirmConfirmed) return;

        const onclickAttr = target.getAttribute('onclick') || '';
        const confirmData = target.dataset.confirm;

        let confirmMsg = confirmData;
        if (!confirmMsg && onclickAttr.includes('confirm(')) {
            const match = onclickAttr.match(/confirm\(['"](.*?)['"]\)/);
            if (match && match[1]) {
                confirmMsg = match[1];
            }
        }

        if (confirmMsg) {
            e.preventDefault();
            e.stopPropagation();

            const title = target.getAttribute('title') || 'Confirm Action';

            showConfirmModal({
                title: title,
                message: confirmMsg,
                confirmText: 'Delete',
                onConfirm: () => {
                    target.dataset.confirmConfirmed = 'true';
                    if (target.tagName === 'A' && target.href) {
                        window.location.href = target.href;
                    } else if (target.form) {
                        target.form.submit();
                    } else {
                        target.click();
                    }
                }
            });
        }
    }, true);

    // INTERCEPT NATIVE BROWSER CONFIRMS ON FORMS AUTOMATICALLY
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.dataset.confirmConfirmed) return;

        const onsubmitAttr = form.getAttribute('onsubmit');
        const confirmData = form.dataset.confirm;

        let confirmMsg = confirmData;
        if (!confirmMsg && onsubmitAttr && onsubmitAttr.includes('confirm(')) {
            const match = onsubmitAttr.match(/confirm\(['"](.*?)['"]\)/);
            if (match && match[1]) {
                confirmMsg = match[1];
            }
        }

        if (confirmMsg) {
            e.preventDefault();
            e.stopImmediatePropagation();

            showConfirmModal({
                title: 'Confirm Action',
                message: confirmMsg,
                confirmText: 'Delete',
                onConfirm: () => {
                    form.dataset.confirmConfirmed = 'true';
                    form.submit();
                }
            });
        }
    }, true);
</script>
</body>

</html>