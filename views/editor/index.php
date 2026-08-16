<div class="row g-4 my-1">
    <div class="col-lg-8">
        <h1 class="h3 mb-3">Take a picture</h1>

        <div class="ratio ratio-4x3 bg-dark rounded overflow-hidden" data-stage>
            <video autoplay muted playsinline data-video></video>

            <?php // The live preview: the very PNG the server will stamp. ?>
            <img alt="" hidden data-overlay-preview>
        </div>

        <p class="alert alert-warning mt-3" hidden data-no-camera>
            No camera is available, so upload a picture instead.
        </p>

        <?php // Radios rather than buttons, so the form still submits with JavaScript off. ?>
        <form method="post" action="/editor" enctype="multipart/form-data" class="mt-4">
            <?= $this->csrfField() ?>

            <fieldset class="mb-4">
                <legend class="h6">Choose an overlay</legend>

                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($overlays as $overlay): ?>
                        <input type="radio" class="btn-check" name="overlay" required autocomplete="off"
                               id="overlay-<?= $this->e($overlay['key']) ?>"
                               value="<?= $this->e($overlay['key']) ?>"
                               data-overlay-src="<?= $this->e($overlay['url']) ?>">

                        <label class="btn btn-outline-secondary p-1" for="overlay-<?= $this->e($overlay['key']) ?>">
                            <img src="<?= $this->e($overlay['url']) ?>" width="96" height="72"
                                 alt="<?= $this->e($overlay['label']) ?>" loading="lazy">
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <?php // Inactive until an overlay is chosen, and driven entirely by editor.js. ?>
            <button type="button" class="btn btn-primary" disabled data-shutter>
                Take the picture
            </button>

            <hr class="my-4">

            <div class="mb-3">
                <label for="photo" class="form-label">No camera? Upload a picture instead</label>
                <input type="file" class="form-control" id="photo" name="photo"
                       accept="image/jpeg,image/png" required>
                <div class="form-text">JPEG or PNG, up to 8&nbsp;MB.</div>
            </div>

            <button type="submit" class="btn btn-outline-primary">Upload</button>
        </form>
    </div>

    <div class="col-lg-4">
        <h2 class="h5 mb-3">Your pictures</h2>

        <p class="text-body-secondary<?= $photos === [] ? '' : ' d-none' ?>" data-panel-empty>
            Nothing yet. Every picture you take shows up here.
        </p>

        <div class="row row-cols-2 row-cols-lg-1 g-3" data-panel>
            <?php foreach ($photos as $photo): ?>
                <?= $this->renderPartial('partials/thumbnail', ['photo' => $photo]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
