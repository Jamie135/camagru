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
    protected array $routes = [

    ];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
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
            return $this->renderContent("Not Found");
        }
        if (is_string($callback)) {
            return $this->renderView($callback);
        }

        return call_user_func($callback);
    }

    public function renderView($view)
    {
        $layoutContent = $this->layoutContent();
        $viewContent = $this->viewContent($view);
        return str_replace('{{content}}', $viewContent, $layoutContent);
    }

    public function renderContent($viewContent)
    {
        $layoutContent = $this->layoutContent();
        return str_replace('{{content}}', $viewContent, $layoutContent);
    }
    protected function layoutContent()
    {
        ob_start();
        include Application::$ROOT_DIR . '/views/layouts/main.php';
        return ob_get_clean();
    }

    protected function viewContent($view)
    {
        ob_start();
        include Application::$ROOT_DIR . '/views/' . $view . '.php';
        return ob_get_clean();
    }
}