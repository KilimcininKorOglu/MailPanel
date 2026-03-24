<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Application settings class. Singleton.
 * Settings are read from the .env or .env.prod file.
 * Only the active backend's connection settings are validated.
 */
class Settings
{
    private static ?self $instance = null;

    public readonly string $backend;
    public readonly string $name;
    public readonly string $secretKey;
    public readonly bool $templatesAutoReload;
    public readonly int $passwordMinLength;
    public readonly bool $passwordIncludesSpecialChars;
    public readonly bool $passwordIncludesNumbers;
    public readonly bool $passwordIncludesLowercase;
    public readonly bool $passwordIncludesUppercase;
    public readonly bool $passwordHashesUsePrefixedScheme;
    public readonly string $passwordDefaultScheme;

    // LDAP settings (populated only when backend=ldap)
    public readonly string $ldapUri;
    public readonly string $ldapRootDn;
    public readonly string $ldapUser;
    public readonly string $ldapPassword;
    public readonly bool $ldapTlsVerify;

    // MySQL settings (populated only when backend=mysql)
    public readonly string $mysqlHost;
    public readonly int $mysqlPort;
    public readonly string $mysqlDatabase;
    public readonly string $mysqlUser;
    public readonly string $mysqlPassword;

    private const ALLOWED_SCHEMES = [
        'PLAIN', 'CRYPT', 'MD5', 'PLAIN-MD5', 'SHA', 'SSHA',
        'SHA512', 'SSHA512', 'SHA512-CRYPT', 'BCRYPT', 'CRAM-MD5', 'NTLM',
    ];

    private function __construct()
    {
        // Backend selection
        $this->backend = strtolower($this->env('MAILPANEL_BACKEND', 'ldap'));
        if (!in_array($this->backend, ['ldap', 'mysql'], true)) {
            throw new \RuntimeException("Unsupported backend: {$this->backend}. Must be 'ldap' or 'mysql'");
        }

        // General settings
        $this->name = $this->env('MAILPANEL_NAME', 'local');
        $this->secretKey = $this->envRequired('MAILPANEL_SECRET_KEY');
        $this->templatesAutoReload = $this->envBool('MAILPANEL_TEMPLATES_AUTO_RELOAD', true);
        $this->passwordMinLength = $this->envInt('MAILPANEL_PASSWORD_MIN_LENGTH', 8);
        $this->passwordIncludesSpecialChars = $this->envBool('MAILPANEL_PASSWORD_INCLUDES_SPECIAL_CHARS', true);
        $this->passwordIncludesNumbers = $this->envBool('MAILPANEL_PASSWORD_INCLUDES_NUMBERS', true);
        $this->passwordIncludesLowercase = $this->envBool('MAILPANEL_PASSWORD_INCLUDES_LOWERCASE', true);
        $this->passwordIncludesUppercase = $this->envBool('MAILPANEL_PASSWORD_INCLUDES_UPPERCASE', true);
        $this->passwordHashesUsePrefixedScheme = $this->envBool('MAILPANEL_PASSWORD_HASHES_USE_PREFIXED_SCHEME', true);

        $scheme = strtoupper($this->env('MAILPANEL_PASSWORD_DEFAULT_SCHEME', 'SSHA512'));
        if (!in_array($scheme, self::ALLOWED_SCHEMES, true)) {
            throw new \RuntimeException("Unsupported password scheme: $scheme");
        }
        $this->passwordDefaultScheme = $scheme;

        // Conditional backend settings
        if ($this->backend === 'ldap') {
            $this->ldapUri = $this->envRequired('MAILPANEL_LDAP_URI');
            $this->ldapRootDn = $this->envRequired('MAILPANEL_LDAP_ROOT_DN');
            $this->ldapUser = $this->envRequired('MAILPANEL_LDAP_USER');
            $this->ldapPassword = $this->envRequired('MAILPANEL_LDAP_PASSWORD');
            $this->ldapTlsVerify = $this->envBool('MAILPANEL_LDAP_TLS_VERIFY', false);

            if (!str_starts_with($this->ldapUri, 'ldap://') && !str_starts_with($this->ldapUri, 'ldaps://')) {
                throw new \RuntimeException("LDAP URI must start with ldap:// or ldaps://");
            }

            $this->mysqlHost = '';
            $this->mysqlPort = 3306;
            $this->mysqlDatabase = '';
            $this->mysqlUser = '';
            $this->mysqlPassword = '';
        } else {
            $this->mysqlHost = $this->envRequired('MAILPANEL_MYSQL_HOST');
            $this->mysqlPort = $this->envInt('MAILPANEL_MYSQL_PORT', 3306);
            $this->mysqlDatabase = $this->envRequired('MAILPANEL_MYSQL_DATABASE');
            $this->mysqlUser = $this->envRequired('MAILPANEL_MYSQL_USER');
            $this->mysqlPassword = $this->envRequired('MAILPANEL_MYSQL_PASSWORD');

            $this->ldapUri = '';
            $this->ldapRootDn = '';
            $this->ldapUser = '';
            $this->ldapPassword = '';
            $this->ldapTlsVerify = false;
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function rawEnv(string $key): string|false
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?? false;
    }

    private function env(string $key, string $default = ''): string
    {
        $value = $this->rawEnv($key);
        return $value !== false ? (string) $value : $default;
    }

    private function envRequired(string $key): string
    {
        $value = $this->rawEnv($key);
        if ($value === false || $value === '') {
            throw new \RuntimeException("Required environment variable $key is not set");
        }
        return (string) $value;
    }

    private function envBool(string $key, bool $default): bool
    {
        $value = $this->rawEnv($key);
        if ($value === false || $value === '') {
            return $default;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function envInt(string $key, int $default): int
    {
        $value = $this->rawEnv($key);
        if ($value === false || $value === '') {
            return $default;
        }
        return (int) $value;
    }
}
