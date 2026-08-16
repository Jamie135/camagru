<?php if ($this->user === null): ?>
    <p class="small text-body-secondary mb-0">
        <a href="/login" class="link-body-secondary">Sign in</a> to leave a comment.
    </p>
<?php else: ?>
    <form method="post" action="/photos/<?= $photo['id'] ?>/comments" data-comment>
        <?= $this->csrfField() ?>
        <input type="hidden" name="return_to" value="<?= $this->e($returnTo) ?>">

        <label class="visually-hidden" for="comment-<?= $photo['id'] ?>">Your comment</label>

        <div class="input-group input-group-sm">
            <textarea class="form-control" id="comment-<?= $photo['id'] ?>" name="body"
                      rows="1" maxlength="1000" required placeholder="Add a comment"></textarea>
            <button class="btn btn-outline-secondary" type="submit">Post</button>
        </div>
    </form>
<?php endif; ?>
