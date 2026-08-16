<h1 class="h3 my-4">Gallery</h1>

<?php if ($photos === []): ?>
    <p class="text-body-secondary">Nothing here yet.</p>
<?php else: ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
        <?php foreach ($photos as $photo): ?>
            <div class="col">
                <?= $this->renderPartial('partials/photo-card', [
                    'photo' => $photo,
                    'returnTo' => '/?page=' . $page,
                ]) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
        <nav aria-label="Gallery pages" class="my-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page === 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="/?page=<?= $page - 1 ?>"
                       <?= $page === 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Previous</a>
                </li>

                <?php for ($n = 1; $n <= $pages; $n++): ?>
                    <li class="page-item <?= $n === $page ? 'active' : '' ?>">
                        <a class="page-link" href="/?page=<?= $n ?>"
                           <?= $n === $page ? 'aria-current="page"' : '' ?>><?= $n ?></a>
                    </li>
                <?php endfor; ?>

                <li class="page-item <?= $page === $pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="/?page=<?= $page + 1 ?>"
                       <?= $page === $pages ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>
