<?php

declare(strict_types=1);

namespace App\Models;

use App\Utils\LdapUtils;

class LdapConnectionException extends \Exception {}

/**
 * LDAP connection singleton. Handles TLS/STARTTLS and admin verification.
 */
class LdapConnection
{
    private static ?self $instance = null;

    /** @var \LDAP\Connection */
    private \LDAP\Connection $conn;

    private function __construct(string $email, string $password)
    {
        $settings = Settings::getInstance();
        $uri = $settings->ldapUri;

        $startTls = false;
        if (str_starts_with($uri, 'ldaps://')) {
            $startTls = true;
            $uri = str_replace('ldaps://', 'ldap://', $uri);
            ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
        }

        $conn = @ldap_connect($uri);
        if ($conn === false) {
            throw new LdapConnectionException("Failed to connect to LDAP server: $uri");
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);

        if ($startTls) {
            if (!@ldap_start_tls($conn)) {
                throw new LdapConnectionException("Failed to start TLS: " . ldap_error($conn));
            }
        }

        $safeEmail = ldap_escape($email, '', LDAP_ESCAPE_DN);

        if (str_contains($email, '@')) {
            $emailDn = LdapUtils::getEmailDn($email);
            if (!@ldap_bind($conn, $emailDn, $password)) {
                throw new \Exception("LDAP bind failed for $email: " . ldap_error($conn));
            }

            $safeEmailFilter = ldap_escape($email, '', LDAP_ESCAPE_FILTER);
            $result = @ldap_read(
                $conn,
                $emailDn,
                "(&(domainGlobalAdmin=yes)(mail={$safeEmailFilter}))",
                ['domainGlobalAdmin']
            );

            if ($result === false || ldap_count_entries($conn, $result) === 0) {
                throw new \Exception("User {$email} is not an administrator!");
            }
        } else {
            $bindDn = "cn={$safeEmail},{$settings->ldapRootDn}";
            if (!@ldap_bind($conn, $bindDn, $password)) {
                throw new \Exception("LDAP bind failed: " . ldap_error($conn));
            }
        }

        $this->conn = $conn;
    }

    public function __destruct()
    {
        if (isset($this->conn)) {
            @ldap_unbind($this->conn);
        }
    }

    /**
     * Creates a new LDAP connection and stores it as the singleton instance.
     */
    public static function connect(string $email, string $password): self
    {
        self::$instance = new self($email, $password);
        return self::$instance;
    }

    /**
     * Returns the existing LDAP connection instance.
     * Throws LdapConnectionException if not connected.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new LdapConnectionException("User authentication has not been performed");
        }
        return self::$instance;
    }

    /**
     * Returns the raw LDAP connection resource for direct LDAP operations.
     */
    public function getConn(): \LDAP\Connection
    {
        return $this->conn;
    }
}
