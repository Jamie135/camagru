<?php

/**
 * This class represents the HTTP request.
 * It provides methods to access information about the incoming request.
 */

namespace app\core;

class Request
{
    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');

        if ($position !== false) {
            $path = substr($path, 0, $position);
        }

        return rtrim($path, '/') ?: '/';
    }


    public function getMethod()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
}