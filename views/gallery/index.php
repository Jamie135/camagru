<div class="d-flex flex-wrap justify-content-between align-items-baseline gap-3">
    <h1 class="page-title">Gallery</h1>

    <?php // How the rest of the photos arrive. Hidden until gallery.js takes it
          // over, since pagination is the only way through without scripting. ?>
    <?php if ($photos !== [] && $pages > 1): ?>
        <div class="mb-4" data-view hidden>
            <div class="btn-group btn-group-sm" role="group" aria-label="How the gallery loads photos">
                <input type="radio" class="btn-check" name="gallery-view" id="view-pages"
                       value="pages" autocomplete="off">
                <label class="btn btn-outline-secondary" for="view-pages">Pages</label>

                <input type="radio" class="btn-check" name="gallery-view" id="view-scroll"
                       value="scroll" autocomplete="off">
                <label class="btn btn-outline-secondary" for="view-scroll">Infinite scroll</label>
            </div>
        </div>
    <?php endif; ?>
</div>

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
