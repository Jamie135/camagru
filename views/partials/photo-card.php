<?php
$href = '/photos/' . $photo['id'];
$returnTo ??= '/';
$thread ??= [];
$preview = 3;
$latest = array_slice($thread, -$preview);
?>
<article class="card h-100" id="photo-<?= $photo['id'] ?>" data-photo="<?= $photo['id'] ?>">
    <a href="<?= $href ?>" class="photo-mat">
        <img src="/uploads/<?= $this->e($photo['filename']) ?>" class="img-fluid"
             alt="Photo by <?= $this->e($photo['author']) ?>"
             width="640" height="480" loading="lazy">
    </a>

    <div class="card-body d-flex flex-column">
        <?php if ($photo['caption'] !== null): ?>
            <p class="card-text placard-title mb-2"><?= nl2br($this->e($photo['caption'])) ?></p>
        <?php endif; ?>

        <p class="card-text placard-author mb-0"><?= $this->e($photo['author']) ?></p>

        <p class="card-text placard-date text-body-secondary mb-3">
            <time datetime="<?= $this->e($photo['created_at']) ?>"><?= $this->e($this->date($photo['created_at'])) ?></time>
        </p>

        <div class="card-text d-flex gap-3 small align-items-baseline border-top pt-2">
            <?= $this->renderPartial('partials/like-button', [
                'photo' => $photo,
                'returnTo' => $returnTo . '#photo-' . $photo['id'],
            ]) ?>

            <a href="<?= $href ?>" class="link-body-secondary text-decoration-none"
               data-comment-count><?= $photo['comments'] ?>
                comment<?= $photo['comments'] === 1 ? '' : 's' ?></a>

            <span class="ms-auto">
                <?= $this->renderPartial('partials/delete-button', [
                    'photo' => $photo,
                    'returnTo' => $returnTo,
                ]) ?>
            </span>
        </div>

        <ul class="list-unstyled mt-3 mb-0" data-thread data-thread-max="<?= $preview ?>">
            <?php foreach ($latest as $comment): ?>
                <?= $this->renderPartial('partials/comment', ['comment' => $comment]) ?>
            <?php endforeach; ?>
        </ul>

        <p class="small mb-0 mt-1<?= count($thread) > $preview ? '' : ' d-none' ?>" data-thread-more>
            <a href="<?= $href ?>" class="link-body-secondary">See all
                <span data-thread-total><?= count($thread) ?></span> comments</a>
        </p>

        <div class="mt-auto pt-2">
            <?= $this->renderPartial('partials/comment-form', [
                'photo' => $photo,
                'returnTo' => $returnTo . '#photo-' . $photo['id'],
            ]) ?>
        </div>
    </div>
</article>
