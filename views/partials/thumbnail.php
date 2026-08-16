<div class="col" id="thumbnail-<?= $photo['id'] ?>" data-photo="<?= $photo['id'] ?>">
    <div class="card h-100">
        <a href="/photos/<?= $photo['id'] ?>">
            <img src="/uploads/<?= $this->e($photo['filename']) ?>" class="card-img-top img-fluid"
                 alt="Your picture from <?= $this->e($this->date($photo['created_at'])) ?>"
                 width="640" height="480" loading="lazy">
        </a>

        <div class="card-body p-2 d-flex justify-content-between align-items-baseline gap-2">
            <time class="small text-body-secondary" datetime="<?= $this->e($photo['created_at']) ?>">
                <?= $this->e($this->date($photo['created_at'])) ?>
            </time>

            <span class="d-flex align-items-baseline gap-2">
                <a class="btn btn-link btn-sm p-0 align-baseline text-decoration-none link-secondary"
                   href="/photos/<?= $photo['id'] ?>/download">
                    Save<span class="visually-hidden"> this picture to your computer</span>
                </a>

                <?= $this->renderPartial('partials/delete-button', [
                    'photo' => $photo,
                    'returnTo' => '/editor',
                ]) ?>
            </span>
        </div>
    </div>
</div>
