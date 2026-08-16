<?php

/**
 * This class represents the HTTP response.
 * It provides methods to manipulate the response sent to the client.
 */

namespace app\core;

class Response
{

    public function setStatusCode(int $code)
    {
        http_response_code($code);
    }

    public function redirect(string $path, int $code = 302): string
    {
        if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
            $path = '/';
        }

        $this->setStatusCode($code);
        header('Location: ' . $path);

        return '';
    }

    public function file(string $path, string $name, string $type): string
    {
        $this->setStatusCode(200);

        header('Content-Type: ' . $type);
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . filesize($path));

        // One person's picture: no shared cache has any business keeping it.
        header('Cache-Control: private, no-store');

        readfile($path);

        return '';
    }

    public function json(array $data, int $code = 200): string
    {
        $this->setStatusCode($code);
        header('Content-Type: application/json; charset=utf-8');

        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
