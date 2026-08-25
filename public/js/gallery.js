// The gallery without page loads: likes, comments and deletions.

const send = async (form) => {
    const response = await fetch(form.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),
    });

    // A crash answers in HTML, so a body that will not parse is a failure too.
    const data = await response.json().catch(() => null);

    if (!response.ok || data === null) {
        throw new Error(data?.error ?? 'Something went wrong. Reload the page and try again.');
    }

    return data;
};

const scopeOf = (form) => form.closest('[data-photo]') ?? document;

const like = async (form) => {
    const data = await send(form);
    const button = form.querySelector('button');

    button.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
    button.classList.toggle('link-danger', data.liked);
    button.classList.toggle('link-body-secondary', !data.liked);

    form.querySelector('[data-like-icon]').textContent = data.liked ? '♥' : '♡';
    form.querySelector('[data-like-count]').textContent = data.likes;
    form.querySelector('[data-like-label]').textContent =
        (data.liked ? 'likes. Unlike' : 'likes. Like') + ' this photo';
};

const comment = async (form) => {
    const data = await send(form);
    const scope = scopeOf(form);
    const thread = scope.querySelector('[data-thread]');
    const max = Number(thread.dataset.threadMax ?? 0);

    thread.insertAdjacentHTML('beforeend', data.html);

    while (max > 0 && thread.children.length > max) {
        thread.firstElementChild.remove();
    }

    const more = scope.querySelector('[data-thread-more]');

    if (more !== null) {
        more.querySelector('[data-thread-total]').textContent = data.comments;
        more.classList.toggle('d-none', data.comments <= max);
    }

    scope.querySelector('[data-comment-count]').textContent =
        data.comments + ' comment' + (data.comments === 1 ? '' : 's');

    form.reset();
};

const destroy = async (form) => {
    delete form.dataset.confirmed;

    await send(form);

    const card = form.closest('[data-photo]');

    if (card === null) {
        window.location.assign(form.elements.return_to.value);

        return;
    }

    const shelf = card.closest('[data-gallery], [data-panel]');

    (card.closest('.col') ?? card).remove();

    if (shelf === null || shelf.querySelector('[data-photo]') !== null) {
        return;
    }

    if (shelf.matches('[data-gallery]')) {
        window.location.reload();

        return;
    }

    document.querySelector('[data-panel-empty]').classList.remove('d-none');
};

const handlers = [
    ['[data-like]', like],
    ['[data-comment]', comment],
    ['[data-delete]', destroy],
];

const run = async (form, handler) => {
    const button = form.querySelector('button');

    button.disabled = true;

    try {
        await handler(form);
    } catch (error) {
        notify(error.message);
    } finally {
        button.disabled = false;
    }
};

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (event.defaultPrevented) {
        return;
    }

    const match = handlers.find(([selector]) => form.matches(selector));

    if (match === undefined) {
        return;
    }

    event.preventDefault();
    run(form, match[1]);
});

// The gallery two ways, whichever was picked: a page at a time, or the next
// page fetched and appended as the "More" button is clicked or scrolled to.

const gallery = document.querySelector('[data-gallery]');
const more = document.querySelector('[data-more]');
const pagination = document.querySelector('[data-pagination]');
const view = document.querySelector('[data-view]');

if (gallery !== null && more !== null && pagination !== null && view !== null) {
    const button = more.querySelector('[data-more-button]');
    const status = more.querySelector('[data-more-status]');
    const key = 'camagru:gallery-view';

    const first = Number(gallery.dataset.page);
    const pages = Number(gallery.dataset.pages);

    let page = first;
    let loading = false;
    let ended = page >= pages;

    // A browser refusing storage still gets the choice, just not the memory of it.
    const remembered = () => {
        try {
            return window.localStorage.getItem(key);
        } catch {
            return null;
        }
    };

    const remember = (mode) => {
        try {
            window.localStorage.setItem(key, mode);
        } catch {
            return;
        }
    };

    const nextPage = async (wanted) => {
        const response = await fetch('/?page=' + wanted, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        }).catch(() => null);

        const data = response === null ? null : await response.json().catch(() => null);

        if (data === null || !response.ok) {
            throw new Error('More photos could not be loaded.');
        }

        return data;
    };

    const load = async () => {
        if (loading || ended) {
            return;
        }

        loading = true;
        button.disabled = true;
        status.textContent = 'Loading more photos…';

        let advanced = false;

        try {
            const data = await nextPage(page + 1);

            gallery.insertAdjacentHTML('beforeend', data.html);
            page = data.page;
            advanced = true;

            if (page >= data.pages) {
                ended = true;
                button.hidden = true;
                status.textContent = 'That is every photo.';
            } else {
                status.textContent = '';
            }
        } catch (error) {
            notify(error.message);
            status.textContent = 'Those did not load. Try again.';
            pagination.hidden = false;
        } finally {
            loading = false;
            button.disabled = false;
        }

        if (advanced && !ended && more.getBoundingClientRect().top < window.innerHeight) {
            await load();
        }
    };

    // Ahead of the fold, so the next page is usually there before it is needed.
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            load();
        }
    }, { rootMargin: '400px' });

    // Landing on a numbered page, scrolling can only carry on from there, so
    // the pages stay up as the way back to the ones above.
    const scroll = () => {
        pagination.hidden = first === 1;
        more.hidden = ended;
        observer.observe(more);
    };

    const paginate = () => {
        observer.unobserve(more);
        more.hidden = true;
        pagination.hidden = false;
    };

    const choose = (mode) => {
        view.querySelectorAll('[name="gallery-view"]').forEach((radio) => {
            radio.checked = radio.value === mode;
        });

        (mode === 'pages' ? paginate : scroll)();
    };

    view.addEventListener('change', (event) => {
        const mode = event.target.value;

        // Scrolling runs from the top of the gallery, pages resume at the last
        // one scrolling reached: either can mean a page other than this one.
        const wanted = mode === 'scroll' ? 1 : page;

        remember(mode);

        if (wanted === first) {
            choose(mode);
        } else {
            window.location.assign(wanted === 1 ? '/' : '/?page=' + wanted);
        }
    });

    view.hidden = false;
    button.addEventListener('click', load);
    choose(remembered() === 'pages' ? 'pages' : 'scroll');
}
