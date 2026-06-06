import './bootstrap';

import Alpine from 'alpinejs';
import { inject } from '@vercel/analytics';

window.Alpine = Alpine;

Alpine.start();

// Inject Vercel Analytics
inject();

if (import.meta.env.PROD) {
    import('virtual:pwa-register').then(({ registerSW }) => {
        registerSW({
            immediate: true,
        });
    });
}

// Premium Custom Modal Alert & Confirm system
window.showAlert = function(message, title = 'Perhatian') {
    const existing = document.getElementById('custom-alert-modal');
    if (existing) existing.remove();

    const modalHtml = `
        <div id="custom-alert-modal" class="fixed inset-0 z-[99999999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-all duration-300" style="z-index: 99999999 !important; background-color: rgba(0, 0, 0, 0.6) !important; backdrop-filter: blur(8px) !important;">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 transform transition-all duration-200 scale-95 opacity-0 flex flex-col items-center text-center" style="border-radius: 24px !important;">
                <div class="w-12 h-12 bg-red-50 text-red-700 rounded-2xl flex items-center justify-center mb-4" style="border-radius: 16px !important;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-base font-black text-gray-900 font-unbounded mb-1" style="font-family: 'Space Grotesk', sans-serif !important;">${title}</h3>
                <p class="text-gray-500 text-xs leading-relaxed mb-6">${message}</p>
                <button id="custom-alert-btn" class="w-full bg-gray-950 hover:bg-gray-800 text-white font-black py-3 px-4 text-xs rounded-2xl transition duration-200" style="border-radius: 16px !important;">
                    OK
                </button>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    const modal = document.getElementById('custom-alert-modal');
    const container = modal.querySelector('div');
    setTimeout(() => {
        container.classList.remove('scale-95', 'opacity-0');
        container.classList.add('scale-100', 'opacity-100');
    }, 10);

    return new Promise((resolve) => {
        document.getElementById('custom-alert-btn').onclick = function() {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.remove();
                resolve();
            }, 200);
        };
    });
};

window.showConfirm = function(message, title = 'Konfirmasi') {
    const existing = document.getElementById('custom-confirm-modal');
    if (existing) existing.remove();

    const modalHtml = `
        <div id="custom-confirm-modal" class="fixed inset-0 z-[99999999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-all duration-300" style="z-index: 99999999 !important; background-color: rgba(0, 0, 0, 0.6) !important; backdrop-filter: blur(8px) !important;">
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-gray-100 transform transition-all duration-200 scale-95 opacity-0 flex flex-col items-center text-center" style="border-radius: 24px !important;">
                <div class="w-12 h-12 bg-red-50 text-red-700 rounded-2xl flex items-center justify-center mb-4" style="border-radius: 16px !important;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-base font-black text-gray-900 font-unbounded mb-1" style="font-family: 'Space Grotesk', sans-serif !important;">${title}</h3>
                <p class="text-gray-500 text-xs leading-relaxed mb-6">${message}</p>
                <div class="grid grid-cols-2 gap-3 w-full">
                    <button id="custom-confirm-cancel" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-black py-3.5 px-4 text-xs rounded-2xl transition duration-200" style="border-radius: 16px !important;">
                        Batal
                    </button>
                    <button id="custom-confirm-ok" class="bg-red-700 hover:bg-red-800 text-white font-black py-3.5 px-4 text-xs rounded-2xl transition duration-200 shadow-md" style="border-radius: 16px !important;">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    const modal = document.getElementById('custom-confirm-modal');
    const container = modal.querySelector('div');
    setTimeout(() => {
        container.classList.remove('scale-95', 'opacity-0');
        container.classList.add('scale-100', 'opacity-100');
    }, 10);

    return new Promise((resolve) => {
        function close(result) {
            container.classList.remove('scale-100', 'opacity-100');
            container.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.remove();
                resolve(result);
            }, 200);
        }

        document.getElementById('custom-confirm-ok').onclick = () => close(true);
        document.getElementById('custom-confirm-cancel').onclick = () => close(false);
    });
};

// Global override for legacy alerts (so library alerts automatically use the beautiful popup)
const nativeAlert = window.alert;
window.alert = function(message) {
    if (typeof message === 'string') {
        window.showAlert(message);
    } else {
        nativeAlert(message);
    }
};

// Intercept forms with data-confirm attribute to show custom premium confirmation
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.dataset.confirmed === 'true') return;

    const confirmMsg = form.getAttribute('data-confirm');
    if (confirmMsg) {
        e.preventDefault();
        e.stopPropagation();

        window.showConfirm(confirmMsg).then(confirmed => {
            if (confirmed) {
                form.dataset.confirmed = 'true';
                
                // Show loading state on the button
                const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                buttons.forEach(button => {
                    if (button.classList.contains('no-loading') || button.classList.contains('pointer-events-none')) return;
                    button.classList.add('pointer-events-none', 'opacity-70', 'cursor-not-allowed');

                    let spinnerColor = 'text-white';
                    if (button.classList.contains('bg-white') || button.classList.contains('text-gray-900') || button.classList.contains('text-gray-700')) {
                        spinnerColor = 'text-gray-900';
                    }

                    const spinnerHtml = `
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 ${spinnerColor} inline-block align-text-bottom" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    `;
                    const text = button.innerText.trim();
                    button.innerHTML = spinnerHtml + ' ' + text;
                });

                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        });
    }
});

// Global Form Submit Button Buffering Loading Animation (respects defaultPrevented)
document.addEventListener('submit', function(e) {
    if (e.defaultPrevented) return;

    const form = e.target;
    // Don't intercept if it is marked as no-loading
    if (form.classList.contains('no-loading')) return;

    const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    buttons.forEach(button => {
        if (button.classList.contains('no-loading') || button.classList.contains('pointer-events-none')) return;

        // Use setTimeout to ensure browser starts submitting the form before disabling the button
        setTimeout(() => {
            if (e.defaultPrevented) return; // double check if canceled in the meantime
            button.classList.add('pointer-events-none', 'opacity-70', 'cursor-not-allowed');

            let spinnerColor = 'text-white';
            if (button.classList.contains('bg-white') || button.classList.contains('text-gray-900') || button.classList.contains('text-gray-700')) {
                spinnerColor = 'text-gray-900';
            } else if (button.classList.contains('text-red-600') || button.classList.contains('text-red-700')) {
                spinnerColor = 'text-red-700';
            } else if (button.classList.contains('text-green-600') || button.classList.contains('text-green-700')) {
                spinnerColor = 'text-green-700';
            }

            const spinnerHtml = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 ${spinnerColor} inline-block align-text-bottom" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            `;

            const text = button.innerText.trim();
            if (text.length > 0) {
                button.innerHTML = spinnerHtml + ' ' + text;
            } else {
                button.innerHTML = spinnerHtml;
            }
        }, 10);
    });
});

// Force reload on back/forward cache restore to prevent stale state / navigation freezes
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});