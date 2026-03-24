<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Application settings class. Singleton.
 * Settings are read from the .env or .env.prod file.
 */
class Settings
{
    private static ?self $instance = null;

    public readonly string $name;
    public readonly string $secretKey;
    public readonly string $ldapUri;
    public readonly string $ldapRootDn;
    public readonly string $ldapUser;
    public readonly string $ldapPassword;
    public readonly bool $templatesAutoReload;
    public readonly int $passwordMinLength;
    public readonly bool $passwordIncludesSpecialChars;
    public readonly bool $passwordIncludesNumbers;
    public readonly bool $passwordIncludesLowercase;
    public readonly bool $passwordIncludesUppercase;
    public readonly bool $passwordHashesUsePrefixedScheme;
    public readonly string $passwordDefaultScheme;

    private const ALLOWED_SCHEMES = [
        'PLAIN', 'CRYPT', 'MD5', 'PLAIN-MD5', 'SHA', 'SSHA',
        'SHA512', 'SSHA512', 'SHA512-CRYPT', 'BCRYPT', 'CRAM-MD5', 'NTLM',
    ];

    private function __construct()
    {
        $this->name = $this->env('MAILPANEL_NAME', 'local');
        $this->secretKey = $this->envRequired('MAILPANEL_SECRET_KEY');
        $this->ldapUri = $this->envRequired('MAILPANEL_LDAP_URI');
        $this->ldapRootDn = $this->envRequired('MAILPANEL_LDAP_ROOT_DN');
        $this->ldapUser = $this->envRequired('MAILPANEL_LDAP_USER');
        $this->ldapPassword = $this->envRequired('MAILPANEL_LDAP_PASSWORD');
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

        if (!str_starts_with($this->ldapUri, 'ldap://') && !str_starts_with($this->ldapUri, 'ldaps://')) {
            throw new \RuntimeException("LDAP URI must start with ldap:// or ldaps://");
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function env(string $key, string $default = ''): string
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
    }

    private function envRequired(string $key): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: '';
        if ($value === '') {
            throw new \RuntimeException("Required environment variable $key is not set");
        }
        return $value;
    }

    private function envBool(string $key, bool $default): bool
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: '';
        if ($value === '') {
            return $default;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function envInt(string $key, int $default): int
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: '';
        if ($value === '') {
            return $default;
        }
        return (int) $value;
    }
}
