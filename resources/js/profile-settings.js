const dialog = document.querySelector('[data-profile-settings]');
if (dialog) {
    const form = dialog.querySelector('form');
    const fields = ['first_name', 'last_name', 'middle_initial'];
    const initial = JSON.parse(dialog.dataset.values);
    fields.forEach(key => { form.elements[key].value = initial[key] || ''; });
    const values = () => Object.fromEntries(fields.map(key => [key, form.elements[key].value]));
    let baseline = JSON.stringify(values());
    let busy = false;
    let opener;
    const message = dialog.querySelector('[data-profile-message]');
    const clearErrors = () => {
        message.textContent = '';
        fields.forEach(key => {
            form.elements[key].removeAttribute('aria-invalid');
            dialog.querySelector(`[data-field-error="${key}"]`).textContent = '';
        });
    };
    const open = trigger => {
        opener = trigger?.closest('details')?.querySelector('summary')
            || (trigger?.closest('#application-sidebar') ? document.querySelector('header details summary') : trigger);
        trigger?.closest('details')?.removeAttribute('open');
        if (!dialog.open) dialog.showModal();
        form.elements.first_name.focus();
    };
    const close = () => {
        if (busy) return;
        if (JSON.stringify(values()) !== baseline && !window.confirm('Discard your unsaved profile changes?')) return;
        const saved = JSON.parse(baseline);
        fields.forEach(key => { form.elements[key].value = saved[key]; });
        clearErrors();
        dialog.close();
        opener?.focus();
    };
    document.querySelectorAll('[data-open-profile]').forEach(trigger => trigger.addEventListener('click', event => {
        event.preventDefault();
        open(trigger);
    }));
    dialog.querySelectorAll('[data-profile-close]').forEach(button => button.addEventListener('click', close));
    dialog.addEventListener('cancel', event => { event.preventDefault(); close(); });
    window.addEventListener('beforeunload', event => {
        if (JSON.stringify(values()) !== baseline) { event.preventDefault(); event.returnValue = ''; }
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy) return;
        busy = true;
        clearErrors();
        const submit = form.querySelector('[type=submit]');
        submit.disabled = true;
        submit.textContent = 'Saving...';
        form.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(form.action, {
                method: 'POST', credentials: 'same-origin',
                headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': form.elements._token.value},
                body: new FormData(form),
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) {
                message.textContent = response.status === 422 ? 'Please correct the highlighted fields.' : 'Your profile could not be saved. Check your connection or sign in again, then retry.';
                let firstError;
                fields.forEach(key => {
                    if (result.errors?.[key]) {
                        form.elements[key].setAttribute('aria-invalid', 'true');
                        dialog.querySelector(`[data-field-error="${key}"]`).textContent = result.errors[key][0];
                        firstError ??= form.elements[key];
                    }
                });
                firstError?.focus();
                return;
            }
            if (typeof result.display_name !== 'string') throw new Error('Invalid response');
            document.querySelectorAll('[data-current-user-name]').forEach(node => { node.textContent = result.display_name; });
            document.querySelectorAll('[data-profile-initials]').forEach(node => node.replaceChildren(result.display_name.slice(0, 1).toUpperCase()));
            fields.forEach(key => {
                form.elements[key].value = form.elements[key].value.trim();
            });
            form.elements.middle_initial.value = form.elements.middle_initial.value.replace(/\.$/, '').toUpperCase();
            baseline = JSON.stringify(values());
            dialog.close();
            opener?.focus();
            const notice = document.querySelector('[data-profile-success]');
            notice.textContent = result.message;
            notice.hidden = false;
            window.setTimeout(() => { notice.hidden = true; }, 6000);
        } catch {
            message.textContent = 'Your profile could not be saved. Your changes are still here; please try again.';
        } finally {
            busy = false;
            submit.disabled = false;
            submit.textContent = 'Save Changes';
            form.removeAttribute('aria-busy');
        }
    });
    if (new URLSearchParams(location.search).get('profile') === 'settings') {
        open(document.querySelector('[data-open-profile]'));
        const url = new URL(location.href);
        url.searchParams.delete('profile');
        history.replaceState(history.state, '', url);
    }
}
