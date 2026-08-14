<?php

/**
 * Gives every PHP diagnostic one outcome: 
 * a full entry in the log, and a generic 500 page for the visitor.
 */

namespace app\core;

use ErrorException;
use Throwable;

class ErrorHandler
{
    private static bool $rendered = false;

    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    // Error handler
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if ((error_reporting() & $severity) === 0) {
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    // Uncaught exceptions handler
    public static function handleException(Throwable $e): void
    {
        error_log(sprintf(
            "Uncaught %s: %s in %s:%d\n%s",
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));

        self::render();
    }

    // Fatal handler
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        if (($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR)) === 0) {
            return;
        }

        self::render();
    }

    private static function render(): void
    {
        if (self::$rendered) {
            return;
        }
        self::$rendered = true;

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (PHP_SAPI === 'cli') {
            return;
        }

        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
        }

        echo self::page();
    }

    // The layout can itself be what failed, so a plain page takes over then.
    private static function page(): string
    {
        try {
            $view = new View();
            $view->title = 'Something went wrong';

            return $view->render('errors/500');
        } catch (Throwable) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            return '<!doctype html><html lang="en"><head><meta charset="utf-8">'
                . '<title>Something went wrong</title></head><body>'
                . '<h1>Something went wrong</h1>'
                . '<p>This page could not be displayed. The problem has been logged.</p>'
                . '</body></html>';
        }
    }
}
