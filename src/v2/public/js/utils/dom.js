// DOM helper utilities

export function createElement(tag, className = '', text = '') {
    const element = document.createElement(tag);
    if (className) element.className = className;
    if (text) element.textContent = text;
    return element;
}

export function clearElement(element) {
    while (element.firstChild) {
        element.removeChild(element.firstChild);
    }
}

export function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

export function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = createElement('div', `toast ${type}`);

    const messageSpan = createElement('span', 'toast-message', message);
    const closeBtn = createElement('button', 'toast-close', '×');

    closeBtn.addEventListener('click', () => toast.remove());

    toast.appendChild(messageSpan);
    toast.appendChild(closeBtn);
    container.appendChild(toast);

    // Auto remove after 5 seconds
    setTimeout(() => toast.remove(), 5000);
}

export function showElement(element) {
    element.style.display = '';
}

export function hideElement(element) {
    element.style.display = 'none';
}

export function clearFormErrors(form) {
    const errorSpans = form.querySelectorAll('.error-text');
    errorSpans.forEach(span => {
        span.textContent = '';
    });

    const errorInputs = form.querySelectorAll('.error');
    errorInputs.forEach(input => {
        input.classList.remove('error');
    });
}

export function showFormErrors(form, errors) {
    clearFormErrors(form);

    Object.keys(errors).forEach(field => {
        const errorSpan = form.querySelector(`[data-field="${field}"]`);
        const input = form.querySelector(`[name="${field}"]`);

        if (errorSpan && errors[field].length > 0) {
            errorSpan.textContent = errors[field][0];
        }

        if (input) {
            input.classList.add('error');
        }
    });
}
