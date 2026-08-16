<div class="row justify-content-center">
    <div class="col-lg-8">
        <p class="my-4"><a href="/" class="link-body-emphasis">&larr; Back to the gallery</a></p>

        <div class="photo-plate">
            <img src="/uploads/<?= $this->e($photo['filename']) ?>" class="img-fluid"
                 alt="Photo by <?= $this->e($photo['author']) ?>" width="640" height="480">
        </div>

        <?php if ($photo['caption'] !== null): ?>
            <p class="placard-title mt-4 mb-2"><?= nl2br($this->e($photo['caption'])) ?></p>
        <?php endif; ?>

        <h1 class="<?= $photo['caption'] === null ? 'h4 mt-4' : 'h6 text-body-secondary' ?> mb-1">
            Photo by <?= $this->e($photo['author']) ?>
        </h1>

        <p class="placard-date text-body-secondary">
            <time datetime="<?= $this->e($photo['created_at']) ?>"><?= $this->e($this->date($photo['created_at'])) ?></time>
        </p>

        <div class="d-flex gap-3 align-items-baseline">
            <?= $this->renderPartial('partials/like-button', [
                'photo' => $photo,
                'returnTo' => '/photos/' . $photo['id'],
            ]) ?>

            <span class="text-body-secondary" data-comment-count><?= $photo['comments'] ?>
                comment<?= $photo['comments'] === 1 ? '' : 's' ?></span>

            <span class="ms-auto">
                <?= $this->renderPartial('partials/delete-button', [
                    'photo' => $photo,
                    'returnTo' => '/',
                ]) ?>
            </span>
        </div>

        <ul class="list-unstyled mt-4" data-thread>
            <?php foreach ($thread as $comment): ?>
                <?= $this->renderPartial('partials/comment', ['comment' => $comment]) ?>
            <?php endforeach; ?>
        </ul>

        <div class="mb-5">
            <?= $this->renderPartial('partials/comment-form', [
                'photo' => $photo,
                'returnTo' => '/photos/' . $photo['id'],
            ]) ?>
        </div>
    </div>
</div>
