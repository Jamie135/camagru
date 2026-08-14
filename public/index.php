<?php

/**
 * This is the entry point of the application.
 * 
 * It initializes the application and handles incoming requests.
 */

require_once __DIR__ . "/../core/autoload.php";
use app\core\Application;

$app = new Application(dirname(__DIR__));

$app->router->get("/", 'home');

$app->router->get("/contact", "contact");

$app->run();