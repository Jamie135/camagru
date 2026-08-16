<?php
$href = '/photos/' . $photo['id'];
$returnTo ??= '/';
$thread ??= [];

$latest = array_slice($thread, -3);
?>
<article class="card h-100" id="photo-<?= $photo['id'] ?>">
    <a href="<?= $href ?>" class="photo-mat">
        <img src="/uploads/<?= $this->e($photo['filename']) ?>" class="img-fluid"
             alt="Photo by <?= $this->e($photo['author']) ?>"
             width="640" height="480" loading="lazy">
    </a>

    <div class="card-body d-flex flex-column">
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

        <?php if ($latest !== []): ?>
            <ul class="list-unstyled mt-3 mb-0" data-thread>
                <?php foreach ($latest as $comment): ?>
                    <?= $this->renderPartial('partials/comment', ['comment' => $comment]) ?>
                <?php endforeach; ?>
            </ul>

            <?php if (count($thread) > count($latest)): ?>
                <p class="small mb-0 mt-1">
                    <a href="<?= $href ?>" class="link-body-secondary">See all <?= count($thread) ?> comments</a>
                </p>
            <?php endif; ?>
        <?php else: ?>
            <ul class="list-unstyled mt-3 mb-0" data-thread></ul>
        <?php endif; ?>

        <div class="mt-auto pt-2">
            <?= $this->renderPartial('partials/comment-form', [
                'photo' => $photo,
                'returnTo' => $returnTo . '#photo-' . $photo['id'],
            ]) ?>
        </div>
    </div>
</article>
