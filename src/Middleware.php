<?php

declare(strict_types=1);

namespace App;

use App\Models\Settings;

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

        // Session timeout check
        $settings = Settings::getInstance();
        $lastActivity = $_SESSION['lastActivity'] ?? 0;
        if ($lastActivity > 0 && (time() - $lastActivity) > $settings->sessionTimeout) {
            $_SESSION = [];
            session_destroy();
            header('Location: /login?expired=1');
            exit;
        }
        $_SESSION['lastActivity'] = time();

        // Session IP change detection
        if ($settings->sessionValidateIp) {
            $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
            $loginIp = $_SESSION['loginIp'] ?? '';
            if ($loginIp !== '' && $currentIp !== $loginIp) {
                $_SESSION = [];
                session_destroy();
                header('Location: /login?ip_changed=1');
                exit;
            }
        }

        // IP restriction check
        if (!empty($settings->allowedIpRanges)) {
            $clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
            if (!self::isIpAllowed($clientIp, $settings->allowedIpRanges)) {
                http_response_code(403);
                echo 'Access denied: IP not allowed';
                exit;
            }
        }

        CsrfProtection::validateToken();
    }

    /**
     * Requires global admin role. Returns 403 if not a global admin.
     */
    public static function globalAdminRequired(): void
    {
        self::loginRequired();

        if (!self::isGlobalAdmin()) {
            http_response_code(403);
            echo 'Access denied: global admin required';
            exit;
        }
    }

    /**
     * Requires at least domain admin role for the specified domain.
     */
    public static function domainAdminRequired(string $domain): void
    {
        self::loginRequired();

        if (!self::isGlobalAdmin() && !self::isDomainAdmin($domain)) {
            http_response_code(403);
            echo 'Access denied: not authorized for this domain';
            exit;
        }
    }

    /**
     * Whether the current session user is a global admin.
     */
    public static function isGlobalAdmin(): bool
    {
        return !empty($_SESSION['isGlobalAdmin']);
    }

    /**
     * Whether the current session user is a domain admin for the specified domain.
     */
    public static function isDomainAdmin(string $domain): bool
    {
        $managedDomains = $_SESSION['managedDomains'] ?? [];
        return in_array($domain, $managedDomains, true);
    }

    /**
     * Checks if an IP address is in the allowed CIDR ranges.
     */
    public static function isIpAllowed(string $ip, string $ranges): bool
    {
        $rangeList = array_map('trim', explode(',', $ranges));
        foreach ($rangeList as $cidr) {
            if ($cidr === '' || self::ipInCidr($ip, $cidr)) {
                return true;
            }
        }
        return false;
    }

    private static function ipInCidr(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) {
            return $ip === $cidr;
        }
        [$subnet, $mask] = explode('/', $cidr, 2);
        $mask = (int) $mask;
        if ($mask === 0) {
            return true;
        }
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        if ($ipLong === false || $subnetLong === false) {
            return false;
        }
        return ($ipLong >> (32 - $mask)) === ($subnetLong >> (32 - $mask));
    }
}
