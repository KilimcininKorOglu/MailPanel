<?php

declare(strict_types=1);

namespace App\Repositories\Ldap;

use App\Models\LdapConnection;
use App\Models\PaginatedResult;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use App\Utils\LdapUtils;

class LdapUserRepository implements UserRepositoryInterface
{
    private const USER_DETAIL_ATTRS = [
        'mail', 'accountStatus', 'domainGlobalAdmin', 'mailQuota', 'uid',
        'cn', 'givenName', 'sn', 'title', 'telephoneNumber', 'mobile', 'employeeNumber',
    ];

    private const USER_LIST_ATTRS = [
        'mail', 'accountStatus', 'domainGlobalAdmin', 'mailQuota', 'uid',
    ];

    public function getUser(string $domain, string $userId): ?User
    {
        $conn = LdapConnection::getInstance()->getConn();
        $baseDn = 'ou=Users,' . LdapUtils::getDomainDn($domain);
        $safeUserId = ldap_escape($userId, '', LDAP_ESCAPE_FILTER);

        $result = @ldap_list(
            $conn,
            $baseDn,
            "(&(objectClass=mailUser)(uid={$safeUserId}))",
            self::USER_DETAIL_ATTRS
        );

        if ($result === false || ldap_count_entries($conn, $result) === 0) {
            return null;
        }

        $entries = ldap_get_entries($conn, $result);
        $normalized = LdapUtils::normalizeEntry($entries[0], self::USER_DETAIL_ATTRS);
        return User::fromLdapEntry($normalized);
    }

    public function getUsers(string $domain): array
    {
        $conn = LdapConnection::getInstance()->getConn();
        $baseDn = 'ou=Users,' . LdapUtils::getDomainDn($domain);
        $safeDomain = ldap_escape($domain, '', LDAP_ESCAPE_FILTER);

        $result = @ldap_list(
            $conn,
            $baseDn,
            "(&(objectClass=mailUser)(!(mail=@{$safeDomain})))",
            self::USER_LIST_ATTRS
        );

        $users = [];
        if ($result !== false) {
            $entries = ldap_get_entries($conn, $result);
            for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
                $normalized = LdapUtils::normalizeEntry($entries[$i], self::USER_LIST_ATTRS);
                $users[] = User::fromLdapEntry($normalized);
            }
        }

        return $users;
    }

    public function updateUser(string $domain, User $user): void
    {
        $conn = LdapConnection::getInstance()->getConn();
        $dn = LdapUtils::getEmailDn("{$user->uid}@{$domain}");

        $mods = [
            LdapUtils::modReplace('domainGlobalAdmin', $user->domainGlobalAdmin ? 'yes' : null),
            LdapUtils::modReplace('mailQuota', (string) ($user->mailQuota * 1024 * 1024)),
            LdapUtils::modReplace('cn', $user->cn ?: null),
            LdapUtils::modReplace('givenName', $user->givenName ?: null),
            LdapUtils::modReplace('sn', $user->sn ?: null),
            LdapUtils::modReplace('employeeNumber', $user->employeeNumber ?: null),
            LdapUtils::modReplace('title', $user->title ?: null),
            LdapUtils::modReplace('telephoneNumber', $user->telephoneNumber ?: null),
            LdapUtils::modReplace('mobile', $user->mobile ?: null),
            LdapUtils::modReplace('accountStatus', $user->accountStatus ? 'active' : 'disabled'),
        ];

        if (!ldap_modify_batch($conn, $dn, $mods)) {
            throw new \RuntimeException('LDAP update failed: ' . ldap_error($conn));
        }
    }

    public function updateUserPassword(string $domain, string $userUid, string $passwordHash): void
    {
        $conn = LdapConnection::getInstance()->getConn();
        $dn = LdapUtils::getEmailDn("{$userUid}@{$domain}");
        if (!ldap_mod_replace($conn, $dn, ['userPassword' => $passwordHash])) {
            throw new \RuntimeException('LDAP password update failed: ' . ldap_error($conn));
        }
    }

    public function createUser(string $domain, User $user, string $passwordHash): void
    {
        throw new \RuntimeException("User creation is not supported for the LDAP backend");
    }

    public function supportsCreateUser(): bool
    {
        return false;
    }

    public function getUsersPaginated(string $domain, int $page, int $perPage, ?string $startsWith = null, ?bool $activeOnly = null, string $sortBy = 'uid', string $sortDir = 'asc'): PaginatedResult
    {
        $conn = LdapConnection::getInstance()->getConn();
        $baseDn = 'ou=Users,' . LdapUtils::getDomainDn($domain);
        $safeDomain = ldap_escape($domain, '', LDAP_ESCAPE_FILTER);

        $filter = "(&(objectClass=mailUser)(!(mail=@{$safeDomain}))";

        if ($startsWith !== null && $startsWith !== '') {
            $safeLetter = ldap_escape($startsWith, '', LDAP_ESCAPE_FILTER);
            $filter .= "(uid={$safeLetter}*)";
        }

        if ($activeOnly === true) {
            $filter .= "(accountStatus=active)";
        } elseif ($activeOnly === false) {
            $filter .= "(accountStatus=disabled)";
        }

        $filter .= ')';

        $result = @ldap_list($conn, $baseDn, $filter, self::USER_LIST_ATTRS);

        $allUsers = [];
        if ($result !== false) {
            $entries = ldap_get_entries($conn, $result);
            for ($i = 0; $i < ($entries['count'] ?? 0); $i++) {
                $normalized = LdapUtils::normalizeEntry($entries[$i], self::USER_LIST_ATTRS);
                $allUsers[] = User::fromLdapEntry($normalized);
            }
        }

        // Sort in PHP
        $sortProperty = match ($sortBy) {
            'mailQuota' => 'mailQuota',
            'accountStatus' => 'accountStatus',
            'cn' => 'cn',
            default => 'uid',
        };
        $descending = strtoupper($sortDir) === 'DESC';
        usort($allUsers, function (User $a, User $b) use ($sortProperty, $descending) {
            $cmp = match ($sortProperty) {
                'mailQuota' => $a->mailQuota <=> $b->mailQuota,
                'accountStatus' => (int) $a->accountStatus <=> (int) $b->accountStatus,
                default => strcmp($a->{$sortProperty}, $b->{$sortProperty}),
            };
            return $descending ? -$cmp : $cmp;
        });

        $totalCount = count($allUsers);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($allUsers, $offset, $perPage);

        return new PaginatedResult($items, $totalCount, $page, $perPage);
    }

    public function deleteUser(string $domain, string $userUid, string $adminEmail): void
    {
        $conn = LdapConnection::getInstance()->getConn();
        $dn = LdapUtils::getEmailDn("{$userUid}@{$domain}");

        if (!@ldap_delete($conn, $dn)) {
            throw new \RuntimeException("LDAP user deletion failed for '{$userUid}@{$domain}': " . ldap_error($conn));
        }
    }
}
