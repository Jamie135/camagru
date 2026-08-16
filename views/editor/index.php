<h1 class="page-title">Take a picture</h1>

<?php // The title sits above the row so the stage and the side panel share a top edge. ?>
<div class="row g-4 my-1">
    <div class="col-lg-8">
        <h2 class="h5 mb-3">Camera</h2>

        <div class="ratio ratio-4x3 viewfinder rounded overflow-hidden" data-stage
             data-width="<?= $width ?>" data-height="<?= $height ?>">
            <video autoplay muted playsinline data-video></video>

            <img alt="The picture you chose" hidden data-upload-preview>

            <img alt="" hidden data-overlay-preview>
        </div>

        <p class="alert alert-warning mt-3" hidden data-no-camera>
            No camera is available, so upload a picture instead.
        </p>

        <form method="post" action="/editor" enctype="multipart/form-data" class="mt-3">
            <?= $this->csrfField() ?>

            <button type="button" class="btn btn-primary mb-4" disabled data-shutter>
                Take the picture
            </button>

            <fieldset class="mb-4">
                <legend class="h6">Choose an overlay</legend>

                <div class="d-flex flex-nowrap gap-2" data-picker>
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

            <hr class="my-4">

            <div class="mb-3">
                <label for="photo" class="form-label">No camera? Upload a picture instead</label>
                <input type="file" class="form-control" id="photo" name="photo"
                       accept="image/jpeg,image/png" required>

                <div class="form-text">
                    JPEG or PNG, up to 8&nbsp;MB.
                    <button type="button" class="btn btn-link btn-sm p-0 align-baseline" hidden data-use-camera>
                        Go back to the camera
                    </button>
                </div>
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

<dialog data-dialog data-capture-dialog class="dialog-wide" aria-labelledby="capture-title">
    <div class="card shadow-lg">
        <div class="card-body">
            <h2 class="card-title h5" id="capture-title">Keep this picture?</h2>

            <div class="ratio ratio-4x3 viewfinder rounded overflow-hidden">
                <img alt="The picture you just took" data-capture-preview>
                <img alt="" data-capture-overlay>
            </div>
        </div>

        <div class="card-footer bg-body-tertiary d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-secondary" data-retake>Take another</button>
            <button type="button" class="btn btn-primary" data-use-capture>Publish it</button>
        </div>
    </div>
</dialog>
