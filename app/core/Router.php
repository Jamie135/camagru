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
    public Session $session;
    public Auth $auth;
    protected array $routes = [

    ];

    public function __construct(Request $request, Response $response, View $view, Session $session, Auth $auth)
    {
        $this->request = $request;
        $this->response = $response;
        $this->view = $view;
        $this->session = $session;
        $this->auth = $auth;
    }

    public function get($path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    // Resolves the incoming request to the appropriate callback based on the request method and path.
    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();
        $routes = $this->routes[$method] ?? [];

        $callback = null;
        $params = [];

        if (isset($routes[$path])) {
            $callback = $routes[$path];
        } else {
            foreach ($routes as $route => $routeCallback) {
                $matched = $this->match($route, $path);

                if ($matched !== null) {
                    $callback = $routeCallback;
                    $params = $matched;
                    break;
                }
            }
        }

        if ($callback === null) {
            $this->response->setStatusCode(404);
            $this->view->title = 'Page not found';

            return $this->view->render('errors/404');
        }

        if (is_array($callback)) {
            [$class, $action] = $callback;
            $callback = [
                new $class($this->request, $this->response, $this->view, $this->session, $this->auth),
                $action,
            ];
        }

        return call_user_func_array($callback, $params);
    }

    // Matches a route pattern against the request path and extracts parameters.
    protected function match(string $route, string $path): ?array
    {
        $routeParts = explode('/', trim($route, '/'));
        $pathParts = explode('/', trim($path, '/'));

        if (count($routeParts) !== count($pathParts)) {
            return null;
        }

        $params = [];

        foreach ($routeParts as $i => $part) {
            if (str_starts_with($part, '{')) {
                $params[trim($part, '{}')] = $pathParts[$i];
            } elseif ($part !== $pathParts[$i]) {
                return null;
            }
        }

        return $params;
    }
}