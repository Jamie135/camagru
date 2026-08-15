<?php

/**
 * Base class for every controller.
 * A controller is a class that handles the logic for a specific part of the application.
 */

namespace app\core;

abstract class Controller
{
    public function __construct(
        protected Request $request,
        protected Response $response,
        protected View $view,
        protected Session $session,
        protected Auth $auth,
        protected Csrf $csrf,
    ) {
    }

    protected function render(string $view, array $params = []): string
    {
        return $this->view->render($view, $params);
    }

    protected function redirect(string $path): string
    {
        return $this->response->redirect($path);
    }

    // Guards for actions that only make sense one side of the login.
    protected function requireAuth(string $to = '/login'): ?string
    {
        if ($this->auth->check()) {
            return null;
        }

        $this->session->flash('warning', 'Please sign in to continue.');

        return $this->redirect($to);
    }

    protected function requireGuest(string $to = '/'): ?string
    {
        return $this->auth->guest() ? null : $this->redirect($to);
    }
}
