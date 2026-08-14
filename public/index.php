<?php

/**
 * This is the entry point of the application.
 * It initializes the application and handles incoming requests.
 */

require_once __DIR__ . "/../app/core/autoload.php";

use app\controllers\SiteController;
use app\core\Application;
use app\core\ErrorHandler;

ErrorHandler::register();

$app = new Application();

$app->router->get("/", [SiteController::class, 'home']);

$app->router->get("/contact", [SiteController::class, 'contact']);

$app->run();