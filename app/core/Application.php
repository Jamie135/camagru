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
    public static Application $app;

    public function __construct()
    {
        self::$app = $this;

        $this->request = new Request();
        $this->response = new Response();
        $this->view = new View();
        $this->router = new Router($this->request, $this->response, $this->view);
    }

    public function run()
    {
        echo $this->router->resolve();
    }
}