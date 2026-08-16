<?php

/**
 * The public gallery: every photo, newest first, a page at a time.
 */

namespace app\controllers;

use app\core\Controller;
use app\models\Photo;

class GalleryController extends Controller
{
    private const PER_PAGE = 6;

    public function index(): string
    {
        $total = Photo::count();
        $pages = max(1, (int) ceil($total / self::PER_PAGE));

        // Anything the query string says is clamped into range, so ?page=0,
        // ?page=-4 and ?page=nonsense land on a real page instead of an error.
        $page = min($pages, max(1, (int) $this->request->query('page', 1)));

        $this->view->title = 'Gallery';

        return $this->render('gallery/index', [
            'photos' => Photo::paginate(self::PER_PAGE, ($page - 1) * self::PER_PAGE, $this->auth->id()),
            'page' => $page,
            'pages' => $pages,
        ]);
    }

    public function show(string $id): string
    {
        $photo = Photo::findById((int) $id, $this->auth->id());

        if ($photo === null) {
            return $this->notFound();
        }

        $this->view->title = 'Photo by ' . $photo['author'];

        return $this->render('gallery/show', ['photo' => $photo]);
    }
}
