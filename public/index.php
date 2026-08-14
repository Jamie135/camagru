<?php

/**
 * This is the entry point of the application.
 * It initializes the application and handles incoming requests.
 */

require_once __DIR__ . "/../app/core/autoload.php";

use app\core\Application;
use app\core\ErrorHandler;

ErrorHandler::register();

$app = new Application(ROOT_DIR);

$app->router->get("/", 'home');

$app->router->get("/contact", "contact");

$app->run();