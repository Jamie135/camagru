<?php

/**
 * This class represents the HTTP request.
 * It provides methods to access information about the incoming request.
 */

namespace app\core;

class Request
{
    // Returns the path of the request, excluding query parameters.
    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');

        if ($position !== false) {
            $path = substr($path, 0, $position);
        }

        return rtrim($path, '/') ?: '/';
    }

    // Returns the HTTP method of the request (e.g., GET, POST).
    public function getMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
}