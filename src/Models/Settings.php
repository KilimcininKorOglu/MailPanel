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
    public readonly string $secretKey;
    public readonly int $passwordMinLength;
    public readonly bool $passwordIncludesSpecialChars;
    public readonly bool $passwordIncludesNumbers;
    public readonly bool $passwordIncludesLowercase;
    public readonly bool $passwordIncludesUppercase;
    public readonly bool $passwordHashesUsePrefixedScheme;
    public readonly string $passwordDefaultScheme;
    public readonly int $paginationPerPage;
    public readonly bool $requireOldPasswordOnChange;
    public readonly int $sessionTimeout;
    public readonly string $allowedIpRanges;
    public readonly bool $sessionValidateIp;
    public readonly bool $checkUpdates;
    public readonly string $geoIpDbPath;
    public readonly bool $apiEnabled;
    public readonly string $apiKey;
    public readonly string $apiAllowedIps;
    public readonly bool $requireDomainOwnershipVerification;

    // Branding
    public readonly string $brandName;
    public readonly string $brandLogoUrl;
    public readonly string $brandFooterText;
    public readonly string $brandPrimaryColor;

    // iRedAdmin database settings (for activity logging)
    public readonly bool $activityLoggingEnabled;
    public readonly string $iredadminDbHost;
    public readonly int $iredadminDbPort;
    public readonly string $iredadminDbName;
    public readonly string $iredadminDbUser;
    public readonly string $iredadminDbPassword;

    // Amavisd integration
    public readonly bool $amavisdEnabled;
    public readonly int $amavisdRemoveQuarantinedInDays;
    public readonly int $amavisdRemoveMaillogInDays;
    public readonly string $amavisdDbHost;
    public readonly int $amavisdDbPort;
    public readonly string $amavisdDbName;
    public readonly string $amavisdDbUser;
    public readonly string $amavisdDbPassword;

    // Fail2ban integration
    public readonly bool $fail2banEnabled;
    public readonly string $fail2banSocket;
    public readonly string $fail2banJails;

    // iRedAPD integration
    public readonly bool $iredapdEnabled;
    public readonly string $iredapdDbHost;
    public readonly int $iredapdDbPort;
    public readonly string $iredapdDbName;
    public readonly string $iredapdDbUser;
    public readonly string $iredapdDbPassword;

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
    public readonly string $vmailPath;
    public readonly string $storageNode;
    public readonly string $mysqlPassword;

    // PostgreSQL settings (populated only when backend=pgsql)
    public readonly string $pgsqlHost;
    public readonly int $pgsqlPort;
    public readonly string $pgsqlDatabase;
    public readonly string $pgsqlUser;
    public readonly string $pgsqlPassword;

    private const ALLOWED_SCHEMES = [
        'PLAIN', 'CRYPT', 'MD5', 'PLAIN-MD5', 'SHA', 'SSHA',
        'SHA512', 'SSHA512', 'SHA512-CRYPT', 'BCRYPT', 'CRAM-MD5', 'NTLM',
    ];

    private function __construct()
    {
        // Backend selection
        $this->backend = strtolower($this->env('MAILPANEL_BACKEND', 'ldap'));
        if (!in_array($this->backend, ['ldap', 'mysql', 'pgsql'], true)) {
            throw new \RuntimeException("Unsupported backend: {$this->backend}. Must be 'ldap', 'mysql', or 'pgsql'");
        }

        // General settings
        $this->secretKey = $this->envRequired('MAILPANEL_SECRET_KEY');
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
        $this->paginationPerPage = $this->envInt('MAILPANEL_PAGINATION_PER_PAGE', 50);
        $this->requireOldPasswordOnChange = $this->envBool('MAILPANEL_REQUIRE_OLD_PASSWORD_ON_CHANGE', false);
        $this->sessionTimeout = $this->envInt('MAILPANEL_SESSION_TIMEOUT', 1800);
        $this->allowedIpRanges = $this->env('MAILPANEL_ALLOWED_IP_RANGES', '');
        $this->sessionValidateIp = $this->envBool('MAILPANEL_SESSION_VALIDATE_IP', false);
        $this->checkUpdates = $this->envBool('MAILPANEL_CHECK_UPDATES', true);
        $this->geoIpDbPath = $this->env('MAILPANEL_GEOIP_DB_PATH', '');
        $this->apiEnabled = $this->envBool('MAILPANEL_API_ENABLED', false);
        $this->apiKey = $this->env('MAILPANEL_API_KEY', '');
        $this->apiAllowedIps = $this->env('MAILPANEL_API_ALLOWED_IPS', '');
        $this->requireDomainOwnershipVerification = $this->envBool('MAILPANEL_REQUIRE_DOMAIN_OWNERSHIP_VERIFICATION', false);

        // Branding
        $this->brandName = $this->env('MAILPANEL_BRAND_NAME', 'MailPanel');
        $this->brandLogoUrl = $this->env('MAILPANEL_BRAND_LOGO_URL', '/static/logo-iredmail.png');
        $this->brandFooterText = $this->env('MAILPANEL_BRAND_FOOTER_TEXT', '');
        $this->brandPrimaryColor = $this->env('MAILPANEL_BRAND_PRIMARY_COLOR', '');

        // Integration DB default port based on backend
        $defaultDbPort = $this->backend === 'pgsql' ? 5432 : 3306;

        // iRedAdmin database (optional, for activity logging)
        $this->activityLoggingEnabled = $this->envBool('MAILPANEL_ACTIVITY_LOGGING_ENABLED', true);
        $this->iredadminDbHost = $this->env('MAILPANEL_IREDADMIN_DB_HOST', '');
        $this->iredadminDbPort = $this->envInt('MAILPANEL_IREDADMIN_DB_PORT', $defaultDbPort);
        $this->iredadminDbName = $this->env('MAILPANEL_IREDADMIN_DB_NAME', 'iredadmin');
        $this->iredadminDbUser = $this->env('MAILPANEL_IREDADMIN_DB_USER', '');
        $this->iredadminDbPassword = $this->env('MAILPANEL_IREDADMIN_DB_PASSWORD', '');

        // Amavisd integration
        $this->amavisdEnabled = $this->envBool('MAILPANEL_AMAVISD_ENABLED', false);
        $this->amavisdRemoveQuarantinedInDays = $this->envInt('MAILPANEL_AMAVISD_REMOVE_QUARANTINED_IN_DAYS', 7);
        $this->amavisdRemoveMaillogInDays = $this->envInt('MAILPANEL_AMAVISD_REMOVE_MAILLOG_IN_DAYS', 7);
        $this->amavisdDbHost = $this->env('MAILPANEL_AMAVISD_DB_HOST', '');
        $this->amavisdDbPort = $this->envInt('MAILPANEL_AMAVISD_DB_PORT', $defaultDbPort);
        $this->amavisdDbName = $this->env('MAILPANEL_AMAVISD_DB_NAME', 'amavisd');
        $this->amavisdDbUser = $this->env('MAILPANEL_AMAVISD_DB_USER', '');
        $this->amavisdDbPassword = $this->env('MAILPANEL_AMAVISD_DB_PASSWORD', '');

        // Fail2ban integration
        $this->fail2banEnabled = $this->envBool('MAILPANEL_FAIL2BAN_ENABLED', false);
        $this->fail2banSocket = $this->env('MAILPANEL_FAIL2BAN_SOCKET', '');
        $this->fail2banJails = $this->env('MAILPANEL_FAIL2BAN_JAILS', 'dovecot,postfix,postfix-sasl');

        // iRedAPD integration
        $this->iredapdEnabled = $this->envBool('MAILPANEL_IREDAPD_ENABLED', false);
        $this->iredapdDbHost = $this->env('MAILPANEL_IREDAPD_DB_HOST', '');
        $this->iredapdDbPort = $this->envInt('MAILPANEL_IREDAPD_DB_PORT', $defaultDbPort);
        $this->iredapdDbName = $this->env('MAILPANEL_IREDAPD_DB_NAME', 'iredapd');
        $this->iredapdDbUser = $this->env('MAILPANEL_IREDAPD_DB_USER', '');
        $this->iredapdDbPassword = $this->env('MAILPANEL_IREDAPD_DB_PASSWORD', '');

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
            $this->pgsqlHost = '';
            $this->pgsqlPort = 5432;
            $this->pgsqlDatabase = '';
            $this->pgsqlUser = '';
            $this->pgsqlPassword = '';
            $this->vmailPath = '/var/vmail';
            $this->storageNode = 'vmail1';
        } elseif ($this->backend === 'pgsql') {
            $this->pgsqlHost = $this->envRequired('MAILPANEL_PGSQL_HOST');
            $this->pgsqlPort = $this->envInt('MAILPANEL_PGSQL_PORT', 5432);
            $this->pgsqlDatabase = $this->envRequired('MAILPANEL_PGSQL_DATABASE');
            $this->pgsqlUser = $this->envRequired('MAILPANEL_PGSQL_USER');
            $this->pgsqlPassword = $this->envRequired('MAILPANEL_PGSQL_PASSWORD');
            $this->vmailPath = $this->env('MAILPANEL_VMAIL_PATH', '/var/vmail');
            $this->storageNode = $this->env('MAILPANEL_STORAGE_NODE', 'vmail1');

            $this->mysqlHost = '';
            $this->mysqlPort = 3306;
            $this->mysqlDatabase = '';
            $this->mysqlUser = '';
            $this->mysqlPassword = '';
            $this->ldapUri = '';
            $this->ldapRootDn = '';
            $this->ldapUser = '';
            $this->ldapPassword = '';
            $this->ldapTlsVerify = false;
        } else {
            $this->mysqlHost = $this->envRequired('MAILPANEL_MYSQL_HOST');
            $this->mysqlPort = $this->envInt('MAILPANEL_MYSQL_PORT', 3306);
            $this->mysqlDatabase = $this->envRequired('MAILPANEL_MYSQL_DATABASE');
            $this->mysqlUser = $this->envRequired('MAILPANEL_MYSQL_USER');
            $this->mysqlPassword = $this->envRequired('MAILPANEL_MYSQL_PASSWORD');
            $this->vmailPath = $this->env('MAILPANEL_VMAIL_PATH', '/var/vmail');
            $this->storageNode = $this->env('MAILPANEL_STORAGE_NODE', 'vmail1');

            $this->pgsqlHost = '';
            $this->pgsqlPort = 5432;
            $this->pgsqlDatabase = '';
            $this->pgsqlUser = '';
            $this->pgsqlPassword = '';
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
