<?php

/**
 * This is the entry point of the application.
 * It initializes the application and handles incoming requests.
 */

require_once __DIR__ . "/../app/core/autoload.php";

use app\controllers\AuthController;
use app\controllers\ProfileController;
use app\controllers\SiteController;
use app\core\Application;
use app\core\ErrorHandler;

ErrorHandler::register();

$app = new Application();

$app->router->get("/", [SiteController::class, 'home']);

$app->router->get("/register", [AuthController::class, 'register']);
$app->router->post("/register", [AuthController::class, 'register']);

$app->router->get("/verify/{token}", [AuthController::class, 'verify']);

$app->router->get("/login", [AuthController::class, 'login']);
$app->router->post("/login", [AuthController::class, 'login']);

// POST only: a link would let any page log our visitors out by embedding it.
$app->router->post("/logout", [AuthController::class, 'logout']);

$app->router->get("/forgot-password", [AuthController::class, 'forgotPassword']);
$app->router->post("/forgot-password", [AuthController::class, 'forgotPassword']);

$app->router->get("/reset-password/{token}", [AuthController::class, 'resetPassword']);
$app->router->post("/reset-password/{token}", [AuthController::class, 'resetPassword']);

$app->router->get("/profile", [ProfileController::class, 'index']);
$app->router->post("/profile/username", [ProfileController::class, 'updateUsername']);
$app->router->post("/profile/email", [ProfileController::class, 'updateEmail']);
$app->router->post("/profile/password", [ProfileController::class, 'updatePassword']);
$app->router->post("/profile/notifications", [ProfileController::class, 'updateNotifications']);
$app->router->post("/profile/cancel-email-change", [ProfileController::class, 'cancelEmailChange']);

// No session needed: mail is often opened in a different browser.
$app->router->get("/profile/confirm-email/{token}", [ProfileController::class, 'confirmEmail']);

$app->run();