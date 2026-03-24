<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Middleware;
use App\Models\LdapConnection;
use App\Models\User;
use App\Models\UserPassword;
use App\TemplateEngine;
use App\Utils\LdapUtils;
use App\Utils\PasswordUtils;

class UserController
{
    private const USER_DETAIL_ATTRS = [
        'mail', 'accountStatus', 'domainGlobalAdmin', 'mailQuota', 'uid',
        'cn', 'givenName', 'sn', 'title', 'telephoneNumber', 'mobile', 'employeeNumber',
    ];

    private const USER_LIST_ATTRS = [
        'mail', 'accountStatus', 'domainGlobalAdmin', 'mailQuota', 'uid',
    ];

    /**
     * Retrieves a User model by user ID and domain name.
     */
    private static function getUserFromLdap(string $domain, string $userId): ?User
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

    /**
     * Retrieves a list of User models for the specified domain.
     */
    private static function getUsersFromLdap(string $domain): array
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

    /**
     * Updates LDAP user attributes with values from User model.
     */
    private static function updateUser(string $domain, User $user): void
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

        ldap_modify_batch($conn, $dn, $mods);
    }

    /**
     * Updates user's password in LDAP.
     */
    private static function updateUserPassword(string $domain, string $userUid, string $passwordHash): void
    {
        $conn = LdapConnection::getInstance()->getConn();
        $dn = LdapUtils::getEmailDn("{$userUid}@{$domain}");
        ldap_mod_replace($conn, $dn, ['userPassword' => $passwordHash]);
    }

    /**
     * Creates a new user in LDAP. (Stub — not implemented, matches Python)
     */
    private static function createUser(string $domain, string $userUid, string $passwordHash): void
    {
        // Not implemented — matches Python stub
    }

    /**
     * Displays the user list page.
     */
    public static function userList(TemplateEngine $tpl, string $domain): void
    {
        Middleware::loginRequired();

        $users = self::getUsersFromLdap($domain);
        usort($users, fn(User $a, User $b) => strcmp($a->uid, $b->uid));

        $tpl->render('userList.php', [
            'domain' => $domain,
            'users' => $users,
        ]);
    }

    /**
     * Displays the user detail/edit page.
     */
    public static function userView(TemplateEngine $tpl, string $domain, string $userUid, string $editMode): void
    {
        Middleware::loginRequired();

        $error = null;
        $validationErrors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if ($editMode === 'general') {
                    $user = User::fromFormData($_POST);
                    self::updateUser($domain, $user);
                    $success = 'Information updated successfully!';
                } elseif ($editMode === 'password') {
                $password = $_POST['password'] ?? '';
                $passwordRepeat = $_POST['password_repeat'] ?? '';
                $validationErrors = UserPassword::validate($password, $passwordRepeat);

                if (empty($validationErrors)) {
                    $passwordHash = PasswordUtils::generatePasswordHash($password);
                    self::updateUserPassword($domain, $userUid, $passwordHash);
                    $success = 'Password updated successfully!';
                }
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        $user = self::getUserFromLdap($domain, $userUid);
        if ($user === null) {
            http_response_code(404);
            $tpl->render('page404.php');
            return;
        }

        $tpl->render('userView.php', [
            'domain' => $domain,
            'user' => $user,
            'error' => $error,
            'validationErrors' => $validationErrors,
            'success' => $success,
            'editMode' => $editMode,
        ]);
    }

    /**
     * Displays the user creation page.
     */
    public static function userCreateView(TemplateEngine $tpl, string $domain): void
    {
        Middleware::loginRequired();

        $validationErrors = [];
        $user = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userUid = $_POST['uid'] ?? '';
            $user = self::getUserFromLdap($domain, $userUid);

            if ($user !== null) {
                $validationErrors['uid'] = "User with identifier {$userUid} already exists";
            } else {
                try {
                    $user = User::fromFormData($_POST);
                    $password = $_POST['password'] ?? '';
                    $passwordRepeat = $_POST['password_repeat'] ?? '';
                    $validationErrors = UserPassword::validate($password, $passwordRepeat);
                } catch (\Exception $e) {
                    $validationErrors['uid'] = $e->getMessage();
                }
            }
        }

        $tpl->render('userCreate.php', [
            'domain' => $domain,
            'validationErrors' => $validationErrors,
            'user' => $user,
        ]);
    }
}
