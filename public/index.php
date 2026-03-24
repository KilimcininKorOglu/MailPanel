<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use App\Controllers\AdminController;
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

// Authentication
$router->addRoute(['GET', 'POST'], '/login', function () use ($tpl) {
    AuthController::loginPage($tpl);
});

$router->addRoute('GET', '/logout', function () {
    AuthController::logout();
});

// Domain management
$router->addRoute('GET', '/domains', function () use ($tpl) {
    DomainController::domainList($tpl);
});

$router->addRoute(['GET', 'POST'], '/domains/create', function () use ($tpl) {
    DomainController::domainCreate($tpl);
});

$router->addRoute(['GET', 'POST'], '/domains/{domain}/edit', function (string $domain) use ($tpl) {
    DomainController::domainView($tpl, $domain);
});

$router->addRoute('POST', '/domains/{domain}/delete', function (string $domain) use ($tpl) {
    DomainController::domainDelete($tpl, $domain);
});

// Admin management
$router->addRoute('GET', '/admins', function () use ($tpl) {
    AdminController::adminList($tpl);
});

$router->addRoute(['GET', 'POST'], '/admins/create', function () use ($tpl) {
    AdminController::adminCreate($tpl);
});

$router->addRoute(['GET', 'POST'], '/admins/{adminEmail}/{editMode}', function (string $adminEmail, string $editMode) use ($tpl) {
    AdminController::adminView($tpl, $adminEmail, $editMode);
});

$router->addRoute('POST', '/admins/{adminEmail}/delete', function (string $adminEmail) use ($tpl) {
    AdminController::adminDelete($tpl, $adminEmail);
});

// User management
$router->addRoute('GET', '/{domain}/users', function (string $domain) use ($tpl) {
    UserController::userList($tpl, $domain);
});

$router->addRoute(['GET', 'POST'], '/{domain}/users/create', function (string $domain) use ($tpl) {
    UserController::userCreateView($tpl, $domain);
});

$router->addRoute('POST', '/{domain}/users/{userUid}/delete', function (string $domain, string $userUid) use ($tpl) {
    UserController::userDelete($tpl, $domain, $userUid);
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
