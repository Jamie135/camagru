<?php

/**
 * The public gallery: every photo, newest first, a page at a time.
 */

namespace app\controllers;

use app\core\Controller;
use app\models\Like;
use app\models\Photo;

class GalleryController extends Controller
{
    private const PER_PAGE = 6;

    public function index(): string
    {
        $total = Photo::count();
        $pages = max(1, (int) ceil($total / self::PER_PAGE));

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

    public function like(string $id): string
    {
        if (($redirect = $this->requireAuth()) !== null) {
            return $redirect;
        }

        $photo = Photo::findById((int) $id);

        if ($photo === null) {
            return $this->notFound();
        }

        $liked = Like::toggle($photo['id'], (int) $this->auth->id());
        $likes = Like::countFor($photo['id']);

        if ($this->request->wantsJson()) {
            return $this->json(['liked' => $liked, 'likes' => $likes]);
        }

        $returnTo = $this->request->post('return_to');

        return $this->redirect(is_string($returnTo) ? $returnTo : '/photos/' . $photo['id']);
    }
}
