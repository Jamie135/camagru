<?php

/**
 * This class initializes the components of the application,
 * including the router, request, and response handling.
 */

namespace app\core;

class Application
{
    public Router $router;
    public Request $request;
    public Response $response;
    public View $view;
    public Session $session;
    public Csrf $csrf;
    public Auth $auth;
    public static Application $app;

    public function __construct()
    {
        self::$app = $this;

        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->csrf = new Csrf($this->session);
        $this->auth = new Auth($this->session);
        $this->view = new View();
        $this->router = new Router($this->request, $this->response, $this->view, $this->session, $this->auth);
        $this->view->flashes = $this->session->takeFlashes();
        $this->view->csrfToken = $this->csrf->token();
        $this->view->user = $this->auth->user();
    }

    public function run()
    {
        // Check CSRF token for POST requests before resolving the route.
        if ($this->request->isPost() && !$this->csrf->verify($this->request->post(Csrf::FIELD))) {
            $this->response->setStatusCode(403);
            $this->view->title = 'Request rejected';

            echo $this->view->render('errors/403');

            return;
        }

        echo $this->router->resolve();
    }
}