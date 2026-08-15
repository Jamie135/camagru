<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($this->title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <a class="navbar-brand" href="/">Camagru</a>

            <button class="navbar-toggler" type="button" data-toggle-target="#nav"
                    aria-controls="nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= $this->path === '/' ? 'active' : '' ?>"
                           <?= $this->path === '/' ? 'aria-current="page"' : '' ?> href="/">Home</a>
                    </li>
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
                            <a class="nav-link <?= $this->path === '/profile' ? 'active' : '' ?>"
                               href="/profile"><?= $this->e($this->user['username']) ?></a>
                        </li>
                        <li class="nav-item">
                            <?php // One click, on every page, because the layout wraps every page. ?>
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

    <main class="container">
        <?php foreach ($this->flashes as $type => $message): ?>
            <div class="alert alert-<?= $this->e($type) ?> alert-dismissible fade show mt-3" role="alert">
                <?= $this->e($message) ?>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>

        <?= $content ?>
    </main>

    <script src="/js/layout.js"></script>
</body>
</html>
