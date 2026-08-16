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

    public function isPost(): bool
    {
        return $this->getMethod() === 'post';
    }

    // Returns the value of a POST parameter, or a default value if it doesn't exist.
    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    // Returns the value of a query string parameter, or a default value if it doesn't exist.
    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function wantsJson(): bool
    {
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
            return true;
        }

        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    }

    // The whole POST body, as handed to a Validator.
    public function body(): array
    {
        return $_POST;
    }

    public function file(string $key): ?array
    {
        $file = $_FILES[$key] ?? null;

        return is_array($file) ? $file : null;
    }
}