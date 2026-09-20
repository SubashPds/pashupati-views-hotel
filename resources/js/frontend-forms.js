const forms = [...document.querySelectorAll('[data-ajax-form]')];
let currencyBusy = false;
let currencyFeedbackTimer;
const retryUntil = new Map();

function retryMessage(seconds) {
    return `Too many requests. Please wait ${seconds} seconds before trying again.`;
}

function showFeedback(form, message, state) {
    const feedback = form.querySelector('[data-form-feedback]');
    feedback.textContent = message;
    feedback.dataset.state = state;
    feedback.hidden = false;
    return feedback;
}

forms.forEach((form, index) => {
    const isCurrency = form.dataset.ajaxForm === 'currency';
    const annotatedFields = new Map();
    if (!isCurrency) form.noValidate = true;

    function clearErrors() {
        annotatedFields.forEach(({ invalid, describedBy }, field) => {
            if (invalid === null) field.removeAttribute('aria-invalid');
            else field.setAttribute('aria-invalid', invalid);
            if (describedBy === null) field.removeAttribute('aria-describedby');
            else field.setAttribute('aria-describedby', describedBy);
        });
        annotatedFields.clear();
        form.querySelectorAll('[data-field-error]').forEach(error => error.remove());
        form.querySelector('[data-form-feedback]').hidden = true;
    }

    function showErrors(errors) {
        const messages = Object.values(errors).flat();
        const feedback = showFeedback(form, messages.join(' ') || 'Please check your details and try again.', 'error');
        let firstField;
        Object.entries(errors).forEach(([name, messages]) => {
            const names = name === 'contact' ? ['email', 'phone'] : [name];
            [...form.elements].filter(field => names.includes(field.name) && field.type !== 'hidden').forEach(field => {
                const error = document.createElement('p');
                error.id = `form-${index}-${field.name}-error`;
                error.dataset.fieldError = '';
                error.className = 'ajax-field-error';
                error.textContent = [].concat(messages).join(' ');
                annotatedFields.set(field, {
                    invalid: field.getAttribute('aria-invalid'),
                    describedBy: field.getAttribute('aria-describedby'),
                });
                field.setAttribute('aria-invalid', 'true');
                field.setAttribute('aria-describedby', [field.getAttribute('aria-describedby'), error.id].filter(Boolean).join(' '));
                field.after(error);
                firstField ??= field;
            });
        });
        return firstField || feedback;
    }

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (form.dataset.submitting === 'true' || (isCurrency && currencyBusy)) return;
        const remaining = Math.ceil(((retryUntil.get(form.dataset.ajaxForm) || 0) - Date.now()) / 1000);
        if (remaining > 0) {
            showFeedback(form, retryMessage(remaining), 'error').focus();
            return;
        }
        clearErrors();
        const data = new FormData(form);
        const submitter = event.submitter;
        if (submitter?.name) data.append(submitter.name, submitter.value);
        const button = submitter || form.querySelector('button[type="submit"]');
        const buttonContents = button ? [...button.childNodes] : [];
        const affectedForms = isCurrency ? forms.filter(item => item.dataset.ajaxForm === 'currency') : [form];
        const controls = affectedForms.flatMap(item => [...item.elements]).map(field => [field, field.disabled]);
        let focusTarget;

        form.dataset.submitting = 'true';
        form.setAttribute('aria-busy', 'true');
        if (isCurrency) {
            currencyBusy = true;
            clearTimeout(currencyFeedbackTimer);
        }
        controls.forEach(([field]) => { field.disabled = true; });
        if (button) button.textContent = isCurrency ? 'Updating…' : 'Sending…';

        try {
            const response = await fetch(form.action, {
                method: form.method.toUpperCase(),
                body: data,
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (response.status === 429) {
                const header = Number(response.headers.get('Retry-After'));
                const seconds = Number.isFinite(header) && header > 0 ? Math.ceil(header) : 60;
                retryUntil.set(form.dataset.ajaxForm, Date.now() + seconds * 1000);
                focusTarget = showFeedback(form, retryMessage(seconds), 'error');
                return;
            }
            if (response.status === 419) {
                focusTarget = showFeedback(form, 'Your session has expired. Refresh the page before submitting again. Your details have not been cleared.', 'error');
                return;
            }
            const result = await response.json();
            if (response.status === 422) {
                focusTarget = showErrors(result.errors || {});
                return;
            }
            if (!response.ok || typeof result.message !== 'string') throw new Error('Unexpected form response');

            if (isCurrency) {
                if (!['NPR', 'INR', 'USD'].includes(result.currency) || !result.prices) throw new Error('Missing currency prices');
                document.querySelectorAll('[data-currency-price]').forEach(element => {
                    const price = result.prices[element.dataset.currencyPrice];
                    if (typeof price === 'string') element.textContent = price;
                });
                document.querySelectorAll('[data-currency-code]').forEach(element => { element.textContent = result.currency; });
                const select = document.getElementById('display-currency');
                if (select) {
                    select.value = result.currency;
                    [...select.options].forEach(option => { option.defaultSelected = option.value === result.currency; });
                }
                document.querySelector('[data-country-prompt]')?.remove();
                const feedbackForm = select?.form || form;
                showFeedback(feedbackForm, result.message, 'success');
                currencyFeedbackTimer = setTimeout(() => { feedbackForm.querySelector('[data-form-feedback]').hidden = true; }, 5000);
            } else {
                form.reset();
                focusTarget = showFeedback(form, result.message, 'success');
            }
        } catch {
            focusTarget = showFeedback(form, isCurrency
                ? 'Unable to update prices. Please check your connection and try again.'
                : 'We could not confirm your enquiry was received. Your details are still here. Please check your connection or contact the hotel.', 'error');
        } finally {
            controls.forEach(([field, disabled]) => { field.disabled = disabled; });
            if (button) button.replaceChildren(...buttonContents);
            delete form.dataset.submitting;
            form.removeAttribute('aria-busy');
            if (isCurrency) {
                currencyBusy = false;
                // A rejected preference must not leave the selector showing different currency from the prices.
                const select = document.getElementById('display-currency');
                if (select) select.value = [...select.options].find(option => option.defaultSelected)?.value || select.value;
            }
            focusTarget?.focus();
        }
    });
});
