<?php

/**
 * The controller for the image editor page, which is the main feature of the site.
 */

namespace app\controllers;

use app\core\Controller;
use app\models\Overlay;
use app\models\Photo;
use app\services\ImageEditor;
use app\services\UnusableImageException;
use PDOException;
use RuntimeException;

class EditorController extends Controller
{
    public function index(): string
    {
        if (($redirect = $this->requireAuth()) !== null) {
            return $redirect;
        }

        $overlays = [];

        foreach (Overlay::all() as $key => $label) {
            $overlays[] = ['key' => $key, 'label' => $label, 'url' => Overlay::url($key)];
        }

        $this->view->title = 'Take a picture';
        $this->view->styles[] = '/css/editor.css';

        return $this->render('editor/index', [
            'overlays' => $overlays,
            'photos' => Photo::panel((int) $this->auth->id()),
        ]);
    }

    public function store(): string
    {
        if (($redirect = $this->requireAuth()) !== null) {
            return $redirect;
        }

        $overlay = $this->request->post('overlay');

        // The disabled button is a courtesy to the user; this is the rule.
        if (!is_string($overlay) || !Overlay::exists($overlay)) {
            return $this->fail('Choose an overlay before taking a picture.');
        }

        try {
            $filename = (new ImageEditor())->compose($this->bytes(), $overlay);
        } catch (UnusableImageException $e) {
            return $this->fail($e->getMessage());
        }

        try {
            $photo = Photo::create((int) $this->auth->id(), $filename);
        } catch (PDOException $e) {
            // Do not leave an orphan file behind if the row never lands.
            $path = ROOT_DIR . '/data/uploads/' . $filename;

            if (is_file($path)) {
                unlink($path);
            }

            throw $e;
        }

        if ($this->request->wantsJson()) {
            return $this->json([
                'html' => $this->view->renderPartial('partials/thumbnail', ['photo' => $photo]),
            ]);
        }

        $this->session->flash('success', 'Your picture has been added to the gallery.');

        return $this->redirect('/editor');
    }

    private function bytes(): string
    {
        $capture = $this->request->post('capture');

        if (!is_string($capture) || $capture === '') {
            return $this->upload();
        }

        if (preg_match('#^data:image/(?:jpeg|png);base64,#', $capture, $prefix) !== 1) {
            throw new UnusableImageException('That capture was not a valid image.');
        }

        // Strict mode: refuses anything outside the base64 alphabet.
        $bytes = base64_decode(substr($capture, strlen($prefix[0])), true);

        if ($bytes === false) {
            throw new UnusableImageException('That capture was not a valid image.');
        }

        return $bytes;
    }

    private function upload(): string
    {
        $file = $this->request->file('photo');
        $error = $file === null ? UPLOAD_ERR_NO_FILE : ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new UnusableImageException('That image is larger than 8 MB.');
        }

        // A missing tmp directory or an unwritable one is ours to fix, not theirs.
        if ($error === UPLOAD_ERR_NO_TMP_DIR || $error === UPLOAD_ERR_CANT_WRITE) {
            throw new RuntimeException('PHP could not store the upload: error ' . $error . '.');
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new UnusableImageException('Take a picture, or choose a file to upload.');
        }

        // Proves tmp_name really is this request's upload, not a path smuggled in.
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new UnusableImageException('That upload could not be read.');
        }

        return file_get_contents($file['tmp_name']);
    }

    private function fail(string $message): string
    {
        if ($this->request->wantsJson()) {
            return $this->json(['error' => $message], 422);
        }

        $this->session->flash('danger', $message);

        return $this->redirect('/editor');
    }
}
