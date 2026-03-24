<?php

declare(strict_types=1);

namespace App\Repositories\Mysql;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;

class MysqlUserRepository implements UserRepositoryInterface
{
    public function getUser(string $domain, string $userId): ?User
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare(
            "SELECT username, name, first_name, last_name,
                    quota, employeeid, mobile, phone, active,
                    isglobaladmin, rank, domain
             FROM mailbox
             WHERE username = :username AND domain = :domain
             LIMIT 1"
        );
        $stmt->execute([
            'username' => "{$userId}@{$domain}",
            'domain' => $domain,
        ]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return self::rowToUser($row);
    }

    public function getUsers(string $domain): array
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare(
            "SELECT username, name, first_name, last_name,
                    quota, employeeid, mobile, phone, active,
                    isglobaladmin, rank, domain
             FROM mailbox
             WHERE domain = :domain AND username NOT LIKE CONCAT('@', :domainFilter)
             ORDER BY username"
        );
        $stmt->execute(['domain' => $domain, 'domainFilter' => $domain]);

        $users = [];
        while ($row = $stmt->fetch()) {
            $users[] = self::rowToUser($row);
        }

        return $users;
    }

    public function updateUser(string $domain, User $user): void
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare(
            "UPDATE mailbox SET
                name = :cn,
                first_name = :givenName,
                last_name = :sn,
                quota = :quota,
                employeeid = :employeeNumber,
                rank = :title,
                mobile = :mobile,
                phone = :telephoneNumber,
                active = :active,
                isglobaladmin = :isGlobalAdmin
             WHERE username = :username AND domain = :domain"
        );
        $stmt->execute([
            'cn' => $user->cn,
            'givenName' => $user->givenName,
            'sn' => $user->sn,
            'quota' => $user->mailQuota,
            'employeeNumber' => $user->employeeNumber,
            'title' => $user->title,
            'mobile' => $user->mobile,
            'telephoneNumber' => $user->telephoneNumber,
            'active' => $user->accountStatus ? 1 : 0,
            'isGlobalAdmin' => $user->domainGlobalAdmin ? 1 : 0,
            'username' => "{$user->uid}@{$domain}",
            'domain' => $domain,
        ]);
    }

    public function updateUserPassword(string $domain, string $userUid, string $passwordHash): void
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare(
            "UPDATE mailbox SET password = :password WHERE username = :username AND domain = :domain"
        );
        $stmt->execute([
            'password' => $passwordHash,
            'username' => "{$userUid}@{$domain}",
            'domain' => $domain,
        ]);
    }

    public function createUser(string $domain, User $user, string $passwordHash): void
    {
        $pdo = MysqlConnection::getInstance()->getPdo();
        $settings = \App\Models\Settings::getInstance();
        $username = "{$user->uid}@{$domain}";

        $stmt = $pdo->prepare(
            "INSERT INTO mailbox
                (username, password, name, first_name, last_name,
                 quota, employeeid, rank, mobile, phone,
                 domain, active, isglobaladmin, storagebasedirectory,
                 storagenode, maildir, local_part)
             VALUES
                (:username, :password, :cn, :givenName, :sn,
                 :quota, :employeeNumber, :title, :mobile, :telephoneNumber,
                 :domain, :active, :isGlobalAdmin, :storageBase,
                 :storageNode, :maildir, :localPart)"
        );
        $stmt->execute([
            'username' => $username,
            'password' => $passwordHash,
            'cn' => $user->cn,
            'givenName' => $user->givenName,
            'sn' => $user->sn,
            'quota' => $user->mailQuota,
            'employeeNumber' => $user->employeeNumber,
            'title' => $user->title,
            'mobile' => $user->mobile,
            'telephoneNumber' => $user->telephoneNumber,
            'domain' => $domain,
            'active' => $user->accountStatus ? 1 : 0,
            'isGlobalAdmin' => $user->domainGlobalAdmin ? 1 : 0,
            'storageBase' => $settings->vmailPath,
            'storageNode' => $settings->storageNode,
            'maildir' => "{$domain}/{$user->uid}/",
            'localPart' => $user->uid,
        ]);
    }

    public function supportsCreateUser(): bool
    {
        return true;
    }

    /**
     * Converts a MySQL row to a User model.
     * Maps: name→cn, first_name→givenName, last_name→sn,
     *       employeeid→employeeNumber, rank→title, phone→telephoneNumber
     */
    private static function rowToUser(array $row): User
    {
        $username = $row['username'] ?? '';
        $uid = str_contains($username, '@') ? explode('@', $username)[0] : $username;

        return new User(
            uid: $uid,
            accountStatus: (bool) ($row['active'] ?? 0),
            mailQuota: (int) ($row['quota'] ?? 0),
            cn: $row['name'] ?? '',
            givenName: $row['first_name'] ?? '',
            sn: $row['last_name'] ?? '',
            employeeNumber: $row['employeeid'] ?? '',
            title: $row['rank'] ?? '',
            mobile: $row['mobile'] ?? '',
            telephoneNumber: $row['phone'] ?? '',
            domainGlobalAdmin: (bool) ($row['isglobaladmin'] ?? 0),
        );
    }
}
