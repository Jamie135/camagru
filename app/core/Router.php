<?php

/**
 * This is where the routing of the application is handled.
 * It maps incoming requests to the appropriate callback functions or views based on the request method and path.
 */

namespace app\core;

class Router
{
    public Request $request;
    public Response $response;
    public View $view;
    protected array $routes = [

    ];

    public function __construct(Request $request, Response $response, View $view)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
    }

    public function get($path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }
    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }
    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        $callback = $this->routes[$method][$path] ?? false;

        if ($callback === false) {
            $this->response->setStatusCode(404);
            $this->view->title = 'Page not found';

            return $this->view->render('errors/404');
        }

        if (is_string($callback)) {
            return $this->view->render($callback);
        }

        return call_user_func($callback);
    }
}