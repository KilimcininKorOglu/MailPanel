<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\LdapConnection;
use App\TemplateEngine;

class AuthController
{
    /**
     * Login page request handler.
     */
    public static function loginPage(TemplateEngine $tpl): void
    {
        $next = $_GET['next'] ?? '/';
        $error = null;
        $email = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (self::authenticateUser($email, $password)) {
                $_SESSION['email'] = $email;
                header("Location: $next");
                exit;
            }
            $error = 'Invalid credentials!';
        }

        $tpl->render('loginPage.php', [
            'next' => $next,
            'error' => $error,
            'email' => $email,
        ]);
    }

    /**
     * Authenticates a user with the given email and password combination.
     */
    private static function authenticateUser(string $email, string $password): bool
    {
        try {
            LdapConnection::connect($email, $password);
            error_log("User {$email} authenticated successfully");
            return true;
        } catch (\Exception $e) {
            error_log("Failed to authenticate user {$email}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Logout request handler.
     */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        header('Location: /login');
        exit;
    }
}
