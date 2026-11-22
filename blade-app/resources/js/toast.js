// Synapse Campus — Toast notifications (vanilla, token-driven)
(function () {
    'use strict';

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function ensureContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'true');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    function showToast(message, type) {
        if (!message) return;
        const container = ensureContainer();
        const toast = document.createElement('div');
        toast.className = 'toast toast-' + (type === 'error' ? 'error' : 'success');
        toast.setAttribute('role', 'status');
        toast.innerHTML =
            '<span class="toast-icon" aria-hidden="true">' + (type === 'error' ? '✕' : '✓') + '</span>' +
            '<span class="toast-message">' + escapeHtml(message) + '</span>' +
            '<button type="button" class="toast-close" aria-label="Cerrar">×</button>';

        container.appendChild(toast);

        // trigger enter animation
        requestAnimationFrame(function () {
            toast.classList.add('toast-show');
        });

        const dismiss = function () {
            toast.classList.remove('toast-show');
            toast.classList.add('toast-hide');
            setTimeout(function () {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 220);
        };

        const timer = setTimeout(dismiss, 4500);
        toast.querySelector('.toast-close').addEventListener('click', function () {
            clearTimeout(timer);
            dismiss();
        });
    }

    function init() {
        const flash = window.__FLASH__ || {};
        if (flash.success) showToast(flash.success, 'success');
        if (flash.error) showToast(flash.error, 'error');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
