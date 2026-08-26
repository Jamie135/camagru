# How the Client and Server Talk to Each Other

A ground-up explanation of the request/response cycle, written for someone with
no web development background and grounded in this project's actual code.

## Two computers that never touch

There are two separate machines in this story, and they cannot see inside each
other.

**The client** is the browser on someone's laptop — Firefox, Chrome. It knows
how to draw a page and run JavaScript, and nothing else. It has no access to the
database, the uploaded files, or the PHP code.

**The server** is the machine running the Docker stack: nginx, PHP, PostgreSQL.
It has the database and the files, and no idea what the person is looking at.

Because they cannot share memory, they only do one thing: **send each other
messages over the network.** That is the entire relationship. Everything else is
built on top of it.

The useful mental image is ordering at a restaurant through a hatch. You write an
order on a slip and push it through. Someone you never see reads the slip, does
work in a kitchen you cannot enter, and pushes a tray back. Then the hatch
closes. If you want anything else, you write another slip.

## The slip and the tray

The message going in is a **request**. It has four parts:

| Part | What it is |
| --- | --- |
| **Method** | The verb. `GET` means "give me something." `POST` means "here is some data, do something with it." This app uses only these two — see the `get()` and `post()` calls in [public/index.php](public/index.php). |
| **Path** | What you want: `/photos/12`, `/login`, `/editor`. |
| **Headers** | Small labels about the request — who you are, what format you would like back. |
| **Body** | Optional cargo: form fields, an uploaded image. `GET` requests carry none; `POST` requests do. |

The message coming back is a **response**, with three parts:

| Part | What it is |
| --- | --- |
| **Status code** | A number saying how it went. 200 = fine, 302 = "go look over there instead", 403 = "not allowed", 404 = "no such thing", 500 = "I crashed." These appear throughout [app/core/Response.php](app/core/Response.php). |
| **Headers** | Labels about the reply, most importantly `Content-Type`, which tells the browser whether it is receiving a web page, an image, or raw data. |
| **Body** | The actual payload: HTML, JSON, or the bytes of a JPEG. |

That is HTTP. Request in, response out, connection closed.

## Following one click all the way through

Someone clicks the ♥ on a photo. Here is every step, in this codebase.

**1. The browser writes the slip.** The JavaScript intercepts the click in
[gallery.js:111](public/js/gallery.js#L111) and calls `send()` at
[gallery.js:3](public/js/gallery.js#L3), which builds a request: method `POST`,
path `/photos/12/like`, a header saying `X-Requested-With: XMLHttpRequest`, and a
body containing the form's fields.

**2. It crosses the network** to the server, arriving at port 80 where nginx is
listening.

**3. nginx sorts the mail.** nginx is a doorman, not a thinker. It looks at the
path and checks its rules in [nginx/default.conf](nginx/default.conf). If the
path were `/uploads/abc.png`, the rule at
[line 9](nginx/default.conf#L9) would have it read that file off disk and send it
straight back — no PHP involved, which is why images are fast. But
`/photos/12/like` is not a file on disk, so
[line 16](nginx/default.conf#L16) applies: hand it to `index.php`. nginx forwards
it to the PHP container over a second connection at
[line 21](nginx/default.conf#L21).

**4. PHP starts from nothing.** This is the part that surprises people: PHP
begins each request as a blank slate. It runs
[public/index.php](public/index.php) top to bottom, rebuilding the entire
application from scratch — every single time. Nothing survives from the previous
request.

**5. The router reads the address.**
[Request.php](app/core/Request.php) pulls the method and path out of the incoming
message. `resolve()` at [Router.php:49](app/core/Router.php#L49) matches them
against the registered routes and finds
[index.php:23](public/index.php#L23) — the `{id}` placeholder captures `12` via
`match()` at [Router.php:102](app/core/Router.php#L102).

**6. The controller does the actual work.** `like()` at
[GalleryController.php:69](app/controllers/GalleryController.php#L69) checks the
user is logged in, flips the like in the database, and counts the new total.

**7. It picks a reply format.** Here `wantsJson()` matters. Because the request
carried that `X-Requested-With` header, the controller returns
`{"liked": true, "likes": 8}` instead of a whole web page. Compact data, not
markup.

**8. The tray goes back** through nginx to the browser, and PHP throws away
everything it just built.

**9. The browser patches the page.** `like()` at
[gallery.js:22](public/js/gallery.js#L22) reads those two numbers and rewrites
just the heart and the counter. The page never reloaded, never flickered.

## Two ways to hold the conversation

This app uses both, deliberately.

**The whole-page way.** Submit the login form and the browser sends a POST,
receives a complete HTML document, throws away the current page and draws the new
one. Simple, works with JavaScript disabled, but the screen blanks and reloads.

**The background way.** JavaScript sends the request itself with `fetch()`, gets
back a small JSON reply, and edits the existing page in place. That covers likes,
comments, deletions, and infinite scroll.

Notice what the controllers do about this: `index()` at
[GalleryController.php:38](app/controllers/GalleryController.php#L38) returns
*just the photo cards* as HTML when JavaScript asked, and the *entire page* when
the browser asked directly. Same route, same code, two audiences. That is why the
gallery works either way — infinite scroll if JS is on, numbered page links if it
is not.

## The server has amnesia

Since PHP rebuilds itself every request and remembers nothing, how does it know
you are logged in on the second page?

It does not — you tell it, every time.

When you log in, the server generates a random ID, files your details under it on
the server side, and sends the ID back as a **cookie**: a small piece of text it
asks the browser to keep. See `session_start()` at
[Session.php:28](app/core/Session.php#L28).

From then on the browser attaches that cookie to *every* request automatically.
The server looks up the ID, finds your details, and knows who you are. It is a
coat check ticket: the ticket is meaningless, but it points at your coat.

The cookie settings at [Session.php:20](app/core/Session.php#L20) are worth
understanding — `httponly` means JavaScript cannot read the cookie, so a script
injected into a page cannot steal someone's session.

## Why CSRF tokens exist

Here is the awkward consequence. The browser attaches your cookie to every
request to this site — *including requests triggered by a completely different
website*. A malicious page could contain a hidden form that POSTs to
`/photos/12/delete`, and the server would see a perfectly valid, logged-in
request.

The fix is at [Application.php:48](app/core/Application.php#L48): every form gets
a secret random token that only this site's own pages know. An attacker's page
cannot read it, so its forged request arrives without the token and is rejected
with a 403.

## The one rule underneath all of it

The server never trusts the client — because it cannot. Anything arriving in a
request was composed on a machine the server does not control, and can be forged:
form fields, headers, the path, the lot. This is why
[Validator.php](app/core/Validator.php) checks every input, why `requireAuth()`
is called inside `like()` rather than merely hiding the button, and why the CSRF
check runs before routing.

The client asks. The server decides.
