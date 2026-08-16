<?php
$href = '/photos/' . $photo['id'];
?>
<article class="card h-100" id="photo-<?= $photo['id'] ?>">
    <a href="<?= $href ?>">
        <img src="/uploads/<?= $this->e($photo['filename']) ?>" class="card-img-top"
             alt="Photo by <?= $this->e($photo['author']) ?>"
             width="640" height="480" loading="lazy">
    </a>

    <div class="card-body d-flex flex-column">
        <p class="card-text mb-1"><strong><?= $this->e($photo['author']) ?></strong></p>

        <p class="card-text text-body-secondary small mb-3">
            <time datetime="<?= $this->e($photo['created_at']) ?>"><?= $this->e($this->date($photo['created_at'])) ?></time>
        </p>

        <p class="card-text mt-auto mb-0 d-flex gap-3 small">
            <span><span aria-hidden="true">&hearts;</span> <?= $photo['likes'] ?>
                <span class="visually-hidden">likes</span></span>
            <a href="<?= $href ?>" class="link-body-emphasis text-decoration-none"><?= $photo['comments'] ?>
                comment<?= $photo['comments'] === 1 ? '' : 's' ?></a>
        </p>
    </div>
</article>
