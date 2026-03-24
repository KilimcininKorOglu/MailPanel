<?php

declare(strict_types=1);

namespace App;

class Middleware
{
    /**
     * Checks if user is authenticated. Redirects to login page if not.
     * Must be called at the top of each protected controller method.
     */
    public static function loginRequired(): void
    {
        if (empty($_SESSION['email'])) {
            $next = urlencode($_SERVER['REQUEST_URI'] ?? '/');
            header("Location: /login?next={$next}");
            exit;
        }

        CsrfProtection::validateToken();
    }
}
