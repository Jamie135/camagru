<?php if ($this->user !== null && (int) $this->user['id'] === (int) $photo['author_id']): ?>
    <form method="post" action="/photos/<?= $photo['id'] ?>/delete" data-delete>
        <?= $this->csrfField() ?>
        <input type="hidden" name="return_to" value="<?= $this->e($returnTo) ?>">

        <button type="submit" class="btn btn-link btn-sm p-0 align-baseline text-decoration-none link-secondary">
            Delete<span class="visually-hidden"> this photo</span>
        </button>
    </form>
<?php endif; ?>
