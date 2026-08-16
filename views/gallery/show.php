<div class="row justify-content-center">
    <div class="col-lg-8">
        <p class="my-4"><a href="/" class="link-body-emphasis">&larr; Back to the gallery</a></p>

        <img src="/uploads/<?= $this->e($photo['filename']) ?>" class="img-fluid rounded"
             alt="Photo by <?= $this->e($photo['author']) ?>" width="640" height="480">

        <h1 class="h4 mt-4 mb-1">Photo by <?= $this->e($photo['author']) ?></h1>

        <p class="text-body-secondary small">
            <time datetime="<?= $this->e($photo['created_at']) ?>"><?= $this->e($this->date($photo['created_at'])) ?></time>
        </p>

        <div class="d-flex gap-3 align-items-baseline">
            <?= $this->renderPartial('partials/like-button', [
                'photo' => $photo,
                'returnTo' => '/photos/' . $photo['id'],
            ]) ?>

            <span class="text-body-secondary"><?= $photo['comments'] ?>
                comment<?= $photo['comments'] === 1 ? '' : 's' ?></span>
        </div>
    </div>
</div>
