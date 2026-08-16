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
    public const CAPTION_MAX = 200;

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
        $this->view->scripts[] = '/js/editor.js';

        $this->view->scripts[] = '/js/gallery.js';

        return $this->render('editor/index', [
            'overlays' => $overlays,
            'photos' => Photo::panel((int) $this->auth->id()),
            'width' => ImageEditor::WIDTH,
            'height' => ImageEditor::HEIGHT,
            'maxOverlays' => Overlay::MAX,
            'maxCaption' => self::CAPTION_MAX,
        ]);
    }

    public function store(): string
    {
        if (($redirect = $this->requireAuth()) !== null) {
            return $redirect;
        }

        $overlays = Overlay::pick($this->request->post('overlay'));

        if ($overlays === []) {
            return $this->fail('Choose an overlay before taking a picture.');
        }

        try {
            $caption = $this->caption();
            $filename = (new ImageEditor())->compose($this->bytes(), $overlays);
        } catch (UnusableImageException $e) {
            return $this->fail($e->getMessage());
        }

        try {
            $photo = Photo::create((int) $this->auth->id(), $filename, $caption);
        } catch (PDOException $e) {
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

    private function caption(): ?string
    {
        $submitted = $this->request->post('caption');

        if (!is_string($submitted)) {
            return null;
        }

        $caption = preg_replace('/[^\P{C}\n]+/u', '', str_replace("\r\n", "\n", $submitted));

        if ($caption === null) {
            throw new UnusableImageException('That caption could not be read as text.');
        }

        $caption = trim(preg_replace('/\n{3,}/', "\n\n", $caption));

        if ($caption === '') {
            return null;
        }

        if (mb_strlen($caption) > self::CAPTION_MAX) {
            throw new UnusableImageException(
                'Keep the caption to ' . self::CAPTION_MAX . ' characters or fewer.'
            );
        }

        return $caption;
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

        if ($error === UPLOAD_ERR_NO_TMP_DIR || $error === UPLOAD_ERR_CANT_WRITE) {
            throw new RuntimeException('PHP could not store the upload: error ' . $error . '.');
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new UnusableImageException('Take a picture, or choose a file to upload.');
        }

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
