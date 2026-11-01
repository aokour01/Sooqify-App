import './bootstrap';
import 'bootstrap-icons/font/bootstrap-icons.css';
import './echo';

import { Toast } from 'bootstrap';

function showToast(message, url) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toastEl = document.createElement('div');
    toastEl.className = 'toast border-0 shadow';
    toastEl.setAttribute('role', 'alert');

    const body = document.createElement('div');
    body.className = 'toast-body';

    const link = document.createElement('a');
    link.href = url;
    link.className = 'text-decoration-none';
    link.textContent = message;   // textContent, not innerHTML: ad titles are typed by users

    body.appendChild(link);
    toastEl.appendChild(body);
    container.appendChild(toastEl);

    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    new Toast(toastEl, { delay: 8000 }).show();
}

const bell = document.getElementById('notification-bell');

if (bell && window.Echo) {
    const counter = document.getElementById('notification-count');

    window.Echo.private(`App.Models.User.${bell.dataset.userId}`)
        .notification((notification) => {
            // Raise the red counter
            const next = parseInt(counter.dataset.count || '0', 10) + 1;
            counter.dataset.count = next;
            counter.textContent = next;
            counter.classList.remove('d-none');

            // Pop-up that opens the notification (and marks it read)
            showToast(notification.message, `${bell.dataset.baseUrl}/${notification.id}`);
        });
}
