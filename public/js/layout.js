// The layout uses Bootstrap's CSS only — a CSS framework is tolerated, its
// JavaScript is not — so the two interactive pieces are wired up natively here.

// Mobile navbar. Bootstrap's .collapse / .show pair is plain CSS, so flipping
// the class is all its plugin did for us. Above the lg breakpoint the menu is
// shown regardless and the toggler is hidden, so this only bites on phones.
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
