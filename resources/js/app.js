import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Native validation runs before submit. Restore controls when navigating back.
const restoreAuthForms = () => {
    document.querySelectorAll('[data-auth-form]').forEach(form => {
        form.removeAttribute('aria-busy');
        form.querySelectorAll('[data-loading-label]').forEach(button => {
            if (button.dataset.originalContent) button.innerHTML = button.dataset.originalContent;
            button.disabled = false;
        });
    });
};
document.addEventListener('submit', event => {
    const form = event.target.closest('[data-auth-form]');
    if (!form || event.defaultPrevented) return;
    form.setAttribute('aria-busy', 'true');
    form.querySelectorAll('[data-loading-label]').forEach(button => {
        button.dataset.originalContent = button.innerHTML;
        button.textContent = button.dataset.loadingLabel;
        button.disabled = true;
    });
});
window.addEventListener('pageshow', restoreAuthForms);
document.querySelectorAll('[data-profile-dialog]').forEach(dialog => {
    if (typeof dialog.showModal !== 'function') return;
    dialog.close();
    dialog.showModal();
    dialog.addEventListener('cancel', event => event.preventDefault());
    dialog.querySelector('[autofocus]')?.focus();
});

import './profile-settings';
