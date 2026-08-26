# AJAX in Camagru

How the page updates without reloading — what AJAX is, where it lives in this
codebase, and how to inspect the responses yourself.

## Contents

- [What AJAX is](#what-ajax-is)
- [A full round trip: the like button](#a-full-round-trip-the-like-button)
- [Every AJAX element in this codebase](#every-ajax-element-in-this-codebase)
- [Two design points](#two-design-points)
- [What is *not* AJAX here](#what-is-not-ajax-here)
- [How to inspect an AJAX response](#how-to-inspect-an-ajax-response)

---

## What AJAX is

**AJAX** = *Asynchronous JavaScript And XML*. The name is historical — nobody
sends XML any more — but the technique is unchanged:

> JavaScript sends an HTTP request **in the background**, gets a small reply
> (JSON or an HTML fragment), and **patches part of the DOM** — without the
> browser navigating or reloading the page.

Contrast the two flows for "like a photo":

| | Classic form POST | AJAX |
|---|---|---|
| Trigger | Browser submits the form | JS intercepts with `preventDefault()` |
| Request | Browser navigates away | `fetch()` in the background |
| Reply | A whole HTML page (or a 302 redirect) | JSON: `{"liked":true,"likes":4}` |
| Result | Page reloads, scroll position lost | Only the heart and the count change |

Three things make a request "AJAX":

1. It is issued by **script**, not by browser navigation.
2. It is **asynchronous** — the page stays interactive while it is in flight.
3. The server answers with a **fragment or data**, not a full page.

Modern code uses `fetch()` and JSON. The old `XMLHttpRequest` object is what
gave AJAX its name, and its name survives in this project as the
`X-Requested-With: XMLHttpRequest` header — the conventional flag that tells the
server "answer me with data, not a page".

---

## A full round trip: the like button

### 1. The markup is an ordinary form

`views/partials/like-button.php:7-17` — note `data-like`, the hook JS looks for,
and the CSRF hidden field:

```php
<form method="post" action="/photos/<?= $photo['id'] ?>/like" class="d-inline" data-like>
    <?= $this->csrfField() ?>
    <input type="hidden" name="return_to" value="<?= $this->e($returnTo) ?>">

    <button type="submit" aria-pressed="<?= $photo['liked'] ? 'true' : 'false' ?>" ...>
        <span aria-hidden="true" data-like-icon><?= $photo['liked'] ? '&hearts;' : '&#9825;' ?></span>
        <span data-like-count><?= $photo['likes'] ?></span>
        <span class="visually-hidden" data-like-label>...</span>
    </button>
</form>
```

### 2. JS intercepts the submit

`public/js/gallery.js:111-126` — one delegated listener on `document`, so cards
appended later are covered too. `event.preventDefault()` is the line that
cancels the page load:

```js
document.addEventListener('submit', (event) => {
    const form = event.target;
    const match = handlers.find(([selector]) => form.matches(selector));

    if (match === undefined) {
        return;
    }

    event.preventDefault();
    run(form, match[1]);
});
```

### 3. The background request

`public/js/gallery.js:3-18`:

```js
const send = async (form) => {
    const response = await fetch(form.action, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),
    });

    const data = await response.json().catch(() => null);

    if (!response.ok || data === null) {
        throw new Error(data?.error ?? 'Something went wrong. Reload the page and try again.');
    }

    return data;
};
```

`new FormData(form)` serialises the same fields the browser would have posted —
CSRF token included — so the AJAX path is no weaker than the plain one. The
`.catch(() => null)` guards against a crash that answers in HTML: a body that
will not parse counts as a failure.

### 4. The server sees the flag

`app/core/Request.php:48-55`:

```php
public function wantsJson(): bool
{
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        return true;
    }

    return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}
```

### 5. The controller forks

`app/controllers/GalleryController.php:81-88` — the same business logic, two
shapes of reply:

```php
$liked = Like::toggle($photo['id'], (int) $this->auth->id());
$likes = Like::countFor($photo['id']);

if ($this->request->wantsJson()) {
    return $this->json(['liked' => $liked, 'likes' => $likes]);   // AJAX
}

return $this->back('/photos/' . $photo['id']);                    // 302, full reload
```

`Response::json()` (`app/core/Response.php:114-120`) sets the status code and
the `Content-Type: application/json; charset=utf-8` header, then encodes.

### 6. JS patches the DOM

`public/js/gallery.js:22-34` — no reload; four nodes change:

```js
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
```

---

## Every AJAX element in this codebase

### Client side — the requests

| Feature | Code | Endpoint | Sends | Receives |
|---|---|---|---|---|
| Like / unlike | `public/js/gallery.js:22` | `POST /photos/{id}/like` | `FormData` | `{liked, likes}` |
| Post a comment | `public/js/gallery.js:36` | `POST /photos/{id}/comments` | `FormData` | `{html, comments}` |
| Delete a photo | `public/js/gallery.js:61` | `POST /photos/{id}/delete` | `FormData` | `{deleted}` |
| Infinite scroll | `public/js/gallery.js:165-177` | `GET /?page=N` | query string | `{html, page, pages}` |
| Publish a picture | `public/js/editor.js:177-199` | `POST /editor` | `FormData` (base64 capture **or** uploaded file) | `{html}` or `{error}` |

The four `fetch()` calls — `gallery.js:4`, `gallery.js:166` and `editor.js:178` —
are the literal AJAX calls. Everything else is plumbing around them.

Supporting client-side pieces:

- `public/js/gallery.js:97-109` — `run()`: disables the button while the request
  is in flight, and routes a thrown error to `notify()`.
- `public/js/gallery.js:219-223` — an `IntersectionObserver` that fires the
  next-page fetch 400px before the "More" button reaches the viewport.
- `public/js/layout.js:25-43` — `notify()`: renders an error flash from JS, in
  the shape the server sends one.

### Server side — the JSON answers

- `app/core/Request.php:48` — `wantsJson()`, detects an AJAX request.
- `app/core/Response.php:114` — `json()`, emits the JSON reply.
- `app/core/Controller.php:31` — `json()`, the controller-level shortcut.
- `app/core/Controller.php:37` — `requireAuth()`, returns `401 {"error":…}` to
  an AJAX caller instead of redirecting to `/login`.
- `app/core/Controller.php:58` — `notFound()`, returns `404 {"error":…}`.
- `app/core/Application.php:48-53` — a failed CSRF check returns
  `403 {"error":…}` instead of rendering the 403 page.
- Branching controller actions: `GalleryController::index()` (line 38),
  `like()` (84), `comment()` (107, plus a `422` for validation errors),
  `destroy()` (145), and `EditorController::store()` (82).
- `app/core/View.php:51` — `renderPartial()`, renders a template *without* the
  layout. This is what makes an HTML fragment possible: `partials/photo-list`,
  `partials/comment`, `partials/thumbnail`.

---

## Two design points

### Progressive enhancement

Every AJAX feature here is a real `<form>` with a real `action` first; JS only
intercepts it. With JavaScript disabled the like button still posts, the server
takes the `else` branch, and the visitor gets a redirect back to the page. The
AJAX path is an *upgrade*, not a requirement.

### Two payload styles, chosen deliberately

Liking returns raw data (`{liked, likes}`) because the client only needs to flip
a heart and a number.

Commenting, infinite scroll and publishing return **server-rendered HTML**
(`{html: …}`), because building a comment or a photo card in JS would mean
duplicating the PHP template — and re-implementing its escaping. The server
renders once, the client calls `insertAdjacentHTML`: `gallery.js:42`,
`gallery.js:193`, `editor.js:194`.

---

## What is *not* AJAX here

Worth knowing so the claim is not over-stated:

- Login, register, password reset, and all of `ProfileController.php` are plain
  form POST + redirect.
- The confirm-delete dialog (`public/js/layout.js:45-85`) and `notify()` are
  pure DOM manipulation — no request, so not AJAX.
- `GET /photos/{id}/download` is a browser navigation to a file response, not a
  background fetch.

---

## How to inspect an AJAX response

Three ways, from easiest to most precise. The app runs at `http://localhost:8080`
(`NGINX_PORT` in `.env`).

### 1. Browser DevTools — the Network tab

This is the method to reach for first, and the one to demo.

1. Open `http://localhost:8080` and sign in.
2. Open DevTools (<kbd>F12</kbd>) and go to the **Network** tab.
3. Tick **Preserve log**. This matters: if the log survives, the page never
   navigated — which is itself the proof that the request was AJAX.
4. Filter by **Fetch/XHR**. That filter *only* shows script-issued requests, so
   anything appearing under it is an AJAX call by definition.
5. Click a photo's heart. One new row appears: `like`.

Now read the row:

| Where to look | What proves it is AJAX |
|---|---|
| **Type** column | `fetch` (or `xhr`) — **not** `document`. A `document` row means the browser navigated. |
| No new `document` row | The page was never replaced. |
| **Headers → Request Headers** | `X-Requested-With: XMLHttpRequest` — the flag `Request::wantsJson()` reads. |
| **Headers → Response Headers** | `Content-Type: application/json; charset=utf-8` — set by `Response::json()`. A `text/html` reply means the server took the non-AJAX branch. |
| **Headers → General** | `Status Code: 200`, and **no** `302`. The redirect branch was not taken. |
| **Payload** tab | The `FormData` fields: `csrf_token`, `return_to`. |
| **Response** tab | The raw body: `{"liked":true,"likes":4}` |
| **Preview** tab | The same body, parsed into an expandable tree. |
| **Timing** tab | How long it took — the "asynchronous" part is that the page stayed usable throughout. |

Repeat with the other four features to see the different payload shapes:

- Post a comment → `Response` holds `{"html":"<li…>", "comments":3}`. The
  `html` string is exactly what `partials/comment.php` rendered.
- Scroll to the bottom of the gallery → a `GET /?page=2` row appears with
  `{"html":…,"page":2,"pages":5}`. This one fires *before* you reach the button,
  because of the observer's 400px `rootMargin`.
- Take or upload a picture in the editor → `POST /editor` with `{"html":…}`.
- Delete a photo → `{"deleted":true}`.

**Seeing the error branches.** They are worth showing too:

- Sign out in a second tab, then click a heart in the first → `401` with
  `{"error":"Please sign in to continue."}` (`Controller::requireAuth()`).
- Submit an empty comment → `422` with `{"error":"Comment is required."}`
  (`GalleryController::comment()`).
- Leave the page open past the session's life, then act → `403` with
  `{"error":"Your session has expired. Reload the page and try again."}`
  (`Application::run()`).

Each of those errors ends up as a red flash on the page, via the `throw` in
`send()` → the `catch` in `run()` → `notify()`.

### 2. The DevTools Console — issue one by hand

Useful to prove the endpoint's behaviour in isolation, without clicking anything:

```js
// Fetch page 2 of the gallery exactly as gallery.js does.
const response = await fetch('/?page=2', {
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
});

console.log(response.status);                         // 200
console.log(response.headers.get('content-type'));    // application/json; charset=utf-8

const data = await response.json();

console.log(data.page, data.pages);                   // 2 5
console.log(data.html.slice(0, 200));                 // the fragment
```

And the contrast — the same URL with the header removed:

```js
const plain = await fetch('/?page=2');

console.log(plain.headers.get('content-type'));       // text/html; charset=UTF-8
console.log((await plain.text()).slice(0, 100));      // <!doctype html> … the whole page
```

That pair is the clearest demonstration of the fork in the controller: same
route, same data, two different replies, decided by one header.

To watch every AJAX call the page makes, wrap `fetch` before interacting:

```js
const original = window.fetch;

window.fetch = async (...args) => {
    const response = await original(...args);
    console.log(args[0], response.status, await response.clone().json());

    return response;
};
```

`response.clone()` matters — a body can only be read once, so reading the
original would break the page's own handler.

### 3. curl — from the terminal

**The GET endpoint needs no session**, which makes it the simplest proof:

```bash
# AJAX: a JSON fragment
curl -s -i -H 'X-Requested-With: XMLHttpRequest' 'http://localhost:8080/?page=2' | head -20

# Same URL, no header: the whole HTML page
curl -s -i 'http://localhost:8080/?page=2' | head -20
```

Pretty-print the body with `jq`:

```bash
curl -s -H 'X-Requested-With: XMLHttpRequest' 'http://localhost:8080/?page=2' \
  | jq '{page, pages, html: (.html | .[0:120])}'
```

The `Accept` header works too — `Request::wantsJson()` accepts either flag:

```bash
curl -s -H 'Accept: application/json' 'http://localhost:8080/?page=2' | jq .page
```

**The POST endpoints need a session cookie and a CSRF token.** Log in with a
cookie jar first:

```bash
JAR=$(mktemp)

# The login page hands out a session cookie and a token.
TOKEN=$(curl -s -c "$JAR" http://localhost:8080/login \
  | grep -oP 'name="csrf_token" value="\K[^"]+' | head -1)

curl -s -b "$JAR" -c "$JAR" -X POST http://localhost:8080/login \
  -d "csrf_token=$TOKEN" -d 'login=YOUR_USERNAME' -d 'password=YOUR_PASSWORD' -o /dev/null

# The token rotates on login, so read the fresh one from the layout's meta tag.
TOKEN=$(curl -s -b "$JAR" http://localhost:8080/ \
  | grep -oP 'name="csrf-token" content="\K[^"]+' | head -1)

# Now the AJAX like, against photo 1.
curl -s -b "$JAR" -X POST http://localhost:8080/photos/1/like \
  -H 'X-Requested-With: XMLHttpRequest' \
  -d "csrf_token=$TOKEN" | jq .
# {"liked": true, "likes": 4}

rm "$JAR"
```

Drop the `-H` flag from that last call and curl receives a `302` to `/` instead —
the non-AJAX branch. Add `-i` to see it.

### Response shapes, for reference

| Endpoint | Status | Body |
|---|---|---|
| `GET /?page=N` | 200 | `{"html": "…", "page": 2, "pages": 5}` |
| `POST /photos/{id}/like` | 200 | `{"liked": true, "likes": 4}` |
| `POST /photos/{id}/comments` | 200 | `{"html": "…", "comments": 3}` |
| `POST /photos/{id}/comments` | 422 | `{"error": "Comment is required."}` |
| `POST /photos/{id}/delete` | 200 | `{"deleted": true}` |
| `POST /editor` | 200 | `{"html": "…"}` |
| any, signed out | 401 | `{"error": "Please sign in to continue."}` |
| any, unknown photo | 404 | `{"error": "Not found."}` |
| any POST, bad token | 403 | `{"error": "Your session has expired. …"}` |

### Troubleshooting

| Symptom | Cause |
|---|---|
| Response is a full HTML page | The `X-Requested-With` header did not arrive — `wantsJson()` returned `false`. |
| A `302` in the Network tab | Same cause: the controller took the redirect branch. |
| `403` with an "expired session" error | `csrf_token` was missing, stale, or the session cookie was not sent. |
| `401` on every action | Not signed in; check the cookie jar or the browser session. |
| The row's Type is `document` | The submit was not intercepted — the JS did not load, or the form is missing its `data-like` / `data-comment` / `data-delete` hook. |
| "Something went wrong. Reload…" flash | The body would not parse as JSON — usually a PHP fatal error answering in HTML. Read the raw **Response** tab, not **Preview**. |
