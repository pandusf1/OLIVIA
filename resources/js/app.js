import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import { registerSW } from 'virtual:pwa-register'

registerSW({
    immediate: true,
})

// Global Form Submit Button Buffering Loading Animation
document.addEventListener('submit', function(e) {
    const form = e.target;
    // Don't intercept if it is marked as no-loading
    if (form.classList.contains('no-loading')) return;

    const buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    buttons.forEach(button => {
        if (button.classList.contains('no-loading') || button.disabled) return;

        // Use setTimeout to ensure browser starts submitting the form before disabling the button
        setTimeout(() => {
            button.disabled = true;
            button.classList.add('opacity-70', 'cursor-not-allowed');

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
                // Keep icon buttons or simple labels clean
                button.innerHTML = spinnerHtml + ' ' + text;
            } else {
                button.innerHTML = spinnerHtml;
            }
        }, 10);
    });
});