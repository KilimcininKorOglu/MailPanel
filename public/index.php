<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\BaseController;
use App\Controllers\DomainController;
use App\Controllers\UserController;
use App\Exceptions\BackendConnectionException;
use App\Router;
use App\TemplateEngine;

$tpl = new TemplateEngine(__DIR__ . '/../templates');
$router = new Router();

// Register routes
$router->addRoute('GET', '/', function () {
    header('Location: /domains');
    exit;
});

$router->addRoute('GET', '/domains', function () use ($tpl) {
    DomainController::domainList($tpl);
});

$router->addRoute(['GET', 'POST'], '/login', function () use ($tpl) {
    AuthController::loginPage($tpl);
});

$router->addRoute('GET', '/logout', function () {
    AuthController::logout();
});

$router->addRoute('GET', '/{domain}/users', function (string $domain) use ($tpl) {
    UserController::userList($tpl, $domain);
});

$router->addRoute(['GET', 'POST'], '/{domain}/users/create', function (string $domain) use ($tpl) {
    UserController::userCreateView($tpl, $domain);
});

$router->addRoute(['GET', 'POST'], '/{domain}/users/{userUid}/{editMode}', function (string $domain, string $userUid, string $editMode) use ($tpl) {
    UserController::userView($tpl, $domain, $userUid, $editMode);
});

$router->setNotFoundHandler(function () use ($tpl) {
    BaseController::page404($tpl);
});

// Dispatch request — catch backend connection errors at top level
try {
    $router->dispatch(
        $_SERVER['REQUEST_URI'] ?? '/',
        $_SERVER['REQUEST_METHOD'] ?? 'GET'
    );
} catch (BackendConnectionException $e) {
    header('Location: /logout');
    exit;
}
