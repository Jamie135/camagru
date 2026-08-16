<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= $this->e($this->csrfToken) ?>">
    <title><?= $this->e($this->title) ?></title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Inter:wght@400;600;700&display=swap"> -->

    <link rel="stylesheet" href="/css/theme.css">
    <link rel="stylesheet" href="/css/app.css">

    <?php foreach ($this->styles as $href): ?>
        <link rel="stylesheet" href="<?= $this->e($href) ?>">
    <?php endforeach; ?>
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand me-lg-0 d-inline-flex align-items-center gap-2" href="/">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="1.4" aria-hidden="true">
                    <ellipse cx="12" cy="14" rx="9" ry="5.5"/>
                    <path d="M12 14 L12 8.5"/>
                    <circle cx="12" cy="7" r="2.4" fill="currentColor" stroke="none"/>
                </svg>
                Camagru
            </a>

            <button class="navbar-toggler" type="button" data-toggle-target="#nav"
                    aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav me-auto mx-lg-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= $this->path === '/' ? 'active' : '' ?>"
                           <?= $this->path === '/' ? 'aria-current="page"' : '' ?> href="/">Gallery</a>
                    </li>
                    <?php if ($this->user !== null): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->path === '/editor' ? 'active' : '' ?>"
                               <?= $this->path === '/editor' ? 'aria-current="page"' : '' ?> href="/editor">Studio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->path === '/profile' ? 'active' : '' ?>"
                               href="/profile">Settings</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav mb-2 mb-lg-0 align-items-lg-center">
                    <?php if ($this->user === null): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->path === '/login' ? 'active' : '' ?>" href="/login">Sign in</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $this->path === '/register' ? 'active' : '' ?>" href="/register">Sign up</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <form method="post" action="/logout">
                                <?= $this->csrfField() ?>
                                <button type="submit" class="btn btn-link nav-link">Sign out</button>
                            </form>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container flex-grow-1">
        <?php foreach ($this->flashes as $type => $message): ?>
            <div class="alert alert-<?= $this->e($type) ?> alert-dismissible fade show mt-3" role="alert">
                <?= $this->e($message) ?>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>

        <?= $content ?>
    </main>

    <footer class="bg-body-tertiary border-top mt-5">
        <div class="container py-4 d-flex flex-wrap justify-content-between align-items-center gap-2 small text-body-secondary">
            <p class="mb-0">Camagru &mdash; 42 project</p>

            <p class="mb-0 d-flex align-items-center gap-3">
                <a href="https://github.com/Jamie135/camagru" target="_blank" rel="noopener noreferrer"
                   class="link-body-secondary d-inline-flex align-items-center gap-1 text-decoration-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"
                         fill="currentColor" aria-hidden="true">
                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27s1.36.09 2 .27c1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z"/>
                    </svg>
                    Jamie135/camagru
                </a>
            </p>
        </div>
    </footer>

    <dialog data-dialog data-confirm aria-labelledby="confirm-title">
        <div class="card shadow-lg">
            <div class="card-body">
                <h2 class="card-title h5" id="confirm-title">Delete this picture?</h2>
                <p class="card-text mb-0">It will be removed from the gallery for good. This cannot be undone.</p>
            </div>

            <div class="card-footer bg-body-tertiary d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary" data-confirm-cancel>Cancel</button>
                <button type="button" class="btn btn-danger" data-confirm-ok>Delete</button>
            </div>
        </div>
    </dialog>

    <script src="/js/layout.js"></script>

    <?php foreach ($this->scripts as $src): ?>
        <script src="<?= $this->e($src) ?>" defer></script>
    <?php endforeach; ?>
</body>
</html>
