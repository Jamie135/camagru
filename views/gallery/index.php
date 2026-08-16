<h1 class="page-title">Gallery</h1>

<?php if ($photos === []): ?>
    <p class="text-body-secondary">Nothing here yet.</p>
<?php else: ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4" data-gallery
         data-page="<?= $page ?>" data-pages="<?= $pages ?>">
        <?= $this->renderPartial('partials/photo-list', [
            'photos' => $photos,
            'comments' => $comments,
            'page' => $page,
        ]) ?>
    </div>

    <?php // The whole of the pagination when scripting is off. gallery.js hides
          // it and scrolls the pages in instead, and puts it back if that fails. ?>
    <?php if ($pages > 1): ?>
        <nav aria-label="Gallery pages" class="my-4" data-pagination>
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

    <div class="my-4 text-center" data-more hidden>
        <button type="button" class="btn btn-outline-secondary" data-more-button>
            Load more photos
        </button>

        <p class="text-body-secondary small mt-3 mb-0" role="status" data-more-status></p>
    </div>
<?php endif; ?>
