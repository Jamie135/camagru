document.querySelectorAll('[data-toggle-target]').forEach((toggler) => {
    const menu = document.querySelector(toggler.dataset.toggleTarget);

    if (menu === null) {
        return;
    }

    toggler.addEventListener('click', () => {
        const open = menu.classList.toggle('show');

        toggler.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
});

// Flash messages. Delegated, so alerts added to the page later are covered too.
document.addEventListener('click', (event) => {
    const button = event.target.closest('.alert .btn-close');

    if (button !== null) {
        button.closest('.alert').remove();
    }
});

// A flash message raised from JavaScript, in the shape the server sends one.
const notify = (message) => {
    const alert = document.createElement('div');

    alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
    alert.setAttribute('role', 'alert');
    alert.textContent = message;

    const close = document.createElement('button');

    close.type = 'button';
    close.className = 'btn-close';
    close.setAttribute('aria-label', 'Close');

    alert.append(close);
    document.querySelector('main').prepend(alert);

    // The gallery is long: an alert at the top would otherwise go unseen.
    alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
};

const confirmDialog = document.querySelector('[data-confirm]');

if (confirmDialog !== null) {
    let pending = null;

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-delete]');

        // The second pass, once the question has been answered, goes through.
        if (form === null || form.dataset.confirmed === 'yes') {
            return;
        }

        event.preventDefault();
        pending = form;

        confirmDialog.showModal();
    });

    confirmDialog.querySelector('[data-confirm-ok]').addEventListener('click', () => {
        const form = pending;

        pending = null;
        confirmDialog.close();

        if (form === null) {
            return;
        }

        form.dataset.confirmed = 'yes';
        form.requestSubmit();
    });

    confirmDialog.querySelector('[data-confirm-cancel]').addEventListener('click', () => {
        confirmDialog.close();
    });

    // Covers Escape and the cancel button alike.
    confirmDialog.addEventListener('close', () => {
        pending = null;
    });
}
