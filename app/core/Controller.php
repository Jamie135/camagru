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
    ) {
    }

    protected function render(string $view, array $params = []): string
    {
        return $this->view->render($view, $params);
    }
}
