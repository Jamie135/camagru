<?php

/**
 * Class autoloader for the "app\" namespace, PSR-4 style.
 * app\core\Router -> <project root>/app/core/Router.php
 *
 * Also defines ROOT_DIR, the single source of truth for the project root.
 */

define('ROOT_DIR', dirname(__DIR__, 2));

spl_autoload_register(static function (string $class): void {
    $prefix = 'app\\';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = dirname(__DIR__) . '/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});
