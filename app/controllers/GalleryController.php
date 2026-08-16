<?php

/**
 * The public gallery: every photo, newest first, a page at a time.
 */

namespace app\controllers;

use app\core\Controller;
use app\core\Validator;
use app\models\Comment;
use app\models\Like;
use app\models\Photo;
use app\models\User;
use app\services\CommentMailer;

class GalleryController extends Controller
{
    private const PER_PAGE = 6;

    public function index(): string
    {
        $total = Photo::count();
        $pages = max(1, (int) ceil($total / self::PER_PAGE));

        $page = min($pages, max(1, (int) $this->request->query('page', 1)));

        $this->view->title = 'Gallery';

        $photos = Photo::paginate(self::PER_PAGE, ($page - 1) * self::PER_PAGE, $this->auth->id());

        return $this->render('gallery/index', [
            'photos' => $photos,
            'comments' => Comment::forPhotos(array_column($photos, 'id')),
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

        return $this->render('gallery/show', [
            'photo' => $photo,
            'thread' => Comment::forPhoto($photo['id']),
        ]);
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

        return $this->back('/photos/' . $photo['id']);
    }

    public function comment(string $id): string
    {
        if (($redirect = $this->requireAuth()) !== null) {
            return $redirect;
        }

        $photo = Photo::findById((int) $id);

        if ($photo === null) {
            return $this->notFound();
        }

        $validator = new Validator($this->request->body());
        $validator->required('body', 'Comment')->length('body', 1, 1000, 'Comment');

        if ($validator->fails()) {
            if ($this->request->wantsJson()) {
                return $this->json(['error' => $validator->error('body')], 422);
            }

            $this->session->flash('danger', $validator->error('body'));

            return $this->back('/photos/' . $photo['id']);
        }

        $author = $this->auth->user();
        $comment = Comment::create($photo['id'], (int) $author['id'], $validator->value('body'));

        $this->notifyAuthor($photo, $author, $validator->value('body'));

        if ($this->request->wantsJson()) {
            return $this->json([
                'html' => $this->view->renderPartial('partials/comment', ['comment' => $comment]),
                'comments' => Comment::countFor($photo['id']),
            ]);
        }

        return $this->back('/photos/' . $photo['id']);
    }

    public function destroy(string $id): string
    {
        if (($redirect = $this->requireAuth()) !== null) {
            return $redirect;
        }

        $filename = Photo::deleteOwned((int) $id, (int) $this->auth->id());

        if ($filename === null) {
            return $this->notFound();
        }

        $this->removeFile($filename);

        if ($this->request->wantsJson()) {
            return $this->json(['deleted' => true]);
        }

        $this->session->flash('success', 'Your photo has been deleted.');

        return $this->back('/');
    }

    private function removeFile(string $filename): void
    {
        $path = ROOT_DIR . '/data/uploads/' . basename($filename);

        if (is_file($path)) {
            unlink($path);
        }
    }

    private function notifyAuthor(array $photo, array $commenter, string $body): void
    {
        if ((int) $photo['author_id'] === (int) $commenter['id']) {
            return;
        }

        $author = User::findById((int) $photo['author_id']);

        if ($author === null || !$author['notify_on_comment']) {
            return;
        }

        CommentMailer::fromEnv()->sendNewComment(
            $author['email'],
            $author['username'],
            $commenter['username'],
            (int) $photo['id'],
            $body
        );
    }

    private function back(string $fallback): string
    {
        $returnTo = $this->request->post('return_to');

        return $this->redirect(is_string($returnTo) ? $returnTo : $fallback);
    }
}
