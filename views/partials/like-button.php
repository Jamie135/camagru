<?php if ($this->user === null): ?>
    <span class="text-body-secondary">
        <span aria-hidden="true">&hearts;</span> <?= $photo['likes'] ?>
        <span class="visually-hidden">likes. Sign in to like this photo.</span>
    </span>
<?php else: ?>
    <form method="post" action="/photos/<?= $photo['id'] ?>/like" class="d-inline" data-like>
        <?= $this->csrfField() ?>
        <input type="hidden" name="return_to" value="<?= $this->e($returnTo) ?>">

        <button type="submit" aria-pressed="<?= $photo['liked'] ? 'true' : 'false' ?>"
                class="btn btn-link btn-sm p-0 align-baseline text-decoration-none <?= $photo['liked'] ? 'link-danger' : 'link-body-secondary' ?>">
            <span aria-hidden="true"><?= $photo['liked'] ? '&hearts;' : '&#9825;' ?></span>
            <span data-like-count><?= $photo['likes'] ?></span>
            <span class="visually-hidden"><?= $photo['liked'] ? 'likes. Unlike' : 'likes. Like' ?> this photo</span>
        </button>
    </form>
<?php endif; ?>
