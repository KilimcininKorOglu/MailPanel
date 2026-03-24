<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

use App\Controllers\AdminController;
use App\Controllers\AmavisdController;
use App\Controllers\AuthController;
use App\Controllers\BaseController;
use App\Controllers\DashboardController;
use App\Controllers\DeletedMailboxController;
use App\Controllers\DomainController;
use App\Controllers\Fail2banController;
use App\Controllers\IredapdController;
use App\Controllers\LogController;
use App\Controllers\UserController;
use App\Exceptions\BackendConnectionException;
use App\Router;
use App\TemplateEngine;

$tpl = new TemplateEngine(__DIR__ . '/../templates');
$router = new Router();

// Register routes
$router->addRoute('GET', '/', function () {
    header('Location: /dashboard');
    exit;
});

// Dashboard
$router->addRoute('GET', '/dashboard', function () use ($tpl) {
    DashboardController::dashboard($tpl);
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
    DomainController::domainView($tpl, $domain, 'general');
});

$router->addRoute(['GET', 'POST'], '/domains/{domain}/settings', function (string $domain) use ($tpl) {
    DomainController::domainView($tpl, $domain, 'settings');
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

// Activity log
$router->addRoute('GET', '/logs', function () use ($tpl) {
    LogController::logList($tpl);
});

$router->addRoute('POST', '/logs/delete', function () use ($tpl) {
    LogController::deleteLogs($tpl);
});

// Deleted mailboxes
$router->addRoute('GET', '/deleted-mailboxes', function () use ($tpl) {
    DeletedMailboxController::list($tpl);
});

$router->addRoute('POST', '/deleted-mailboxes/{id}/cancel', function (string $id) use ($tpl) {
    DeletedMailboxController::cancel($tpl, $id);
});

$router->addRoute('POST', '/deleted-mailboxes/{id}/reschedule', function (string $id) use ($tpl) {
    DeletedMailboxController::reschedule($tpl, $id);
});

// User management
$router->addRoute('GET', '/{domain}/users', function (string $domain) use ($tpl) {
    UserController::userList($tpl, $domain);
});

$router->addRoute(['GET', 'POST'], '/{domain}/users/create', function (string $domain) use ($tpl) {
    UserController::userCreateView($tpl, $domain);
});

$router->addRoute('POST', '/{domain}/users/bulk', function (string $domain) use ($tpl) {
    UserController::bulkAction($tpl, $domain);
});

$router->addRoute('POST', '/{domain}/users/{userUid}/delete', function (string $domain, string $userUid) use ($tpl) {
    UserController::userDelete($tpl, $domain, $userUid);
});

$router->addRoute(['GET', 'POST'], '/{domain}/users/{userUid}/{editMode}', function (string $domain, string $userUid, string $editMode) use ($tpl) {
    UserController::userView($tpl, $domain, $userUid, $editMode);
});

// Amavisd integration
$router->addRoute('GET', '/amavisd/quarantine', function () use ($tpl) {
    AmavisdController::quarantineList($tpl);
});

$router->addRoute('POST', '/amavisd/quarantine/{mailId}/release', function (string $mailId) use ($tpl) {
    AmavisdController::releaseMessage($tpl, $mailId);
});

$router->addRoute('POST', '/amavisd/quarantine/{mailId}/delete', function (string $mailId) use ($tpl) {
    AmavisdController::deleteMessage($tpl, $mailId);
});

$router->addRoute('GET', '/amavisd/maillog', function () use ($tpl) {
    AmavisdController::mailLog($tpl);
});

$router->addRoute('POST', '/amavisd/cleanup', function () use ($tpl) {
    AmavisdController::cleanup($tpl);
});

// Fail2ban integration
$router->addRoute('GET', '/fail2ban', function () use ($tpl) {
    Fail2banController::status($tpl);
});

$router->addRoute('POST', '/fail2ban/ban', function () use ($tpl) {
    Fail2banController::banIp($tpl);
});

$router->addRoute('POST', '/fail2ban/unban', function () use ($tpl) {
    Fail2banController::unbanIp($tpl);
});

// iRedAPD integration
$router->addRoute(['GET', 'POST'], '/iredapd/throttle/{account}', function (string $account) use ($tpl) {
    IredapdController::throttleView($tpl, $account);
});

$router->addRoute(['GET', 'POST'], '/iredapd/greylist/{account}', function (string $account) use ($tpl) {
    IredapdController::greylistView($tpl, $account);
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
