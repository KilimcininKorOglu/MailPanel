<?php

declare(strict_types=1);

namespace App\Repositories\Mysql;

use App\Models\PaginatedResult;
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

    public function getUsersPaginated(string $domain, int $page, int $perPage, ?string $startsWith = null, ?bool $activeOnly = null, string $sortBy = 'uid', string $sortDir = 'asc'): PaginatedResult
    {
        $pdo = MysqlConnection::getInstance()->getPdo();
        $offset = ($page - 1) * $perPage;

        $where = "domain = :domain";
        $params = ['domain' => $domain];

        if ($startsWith !== null && $startsWith !== '') {
            $where .= " AND username LIKE :startsWith";
            $params['startsWith'] = strtolower($startsWith) . "%@{$domain}";
        }

        if ($activeOnly === true) {
            $where .= " AND active = 1";
        } elseif ($activeOnly === false) {
            $where .= " AND active = 0";
        }

        // Count
        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM mailbox WHERE {$where}");
        $countStmt->execute($params);
        $totalCount = (int) $countStmt->fetch()['total'];

        // Validate sort column
        $allowedSorts = ['uid' => 'username', 'mailQuota' => 'quota', 'accountStatus' => 'active', 'cn' => 'name'];
        $orderColumn = $allowedSorts[$sortBy] ?? 'username';
        $orderDir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

        $stmt = $pdo->prepare(
            "SELECT username, name, first_name, last_name,
                    quota, employeeid, mobile, phone, active,
                    isglobaladmin, rank, domain
             FROM mailbox
             WHERE {$where}
             ORDER BY {$orderColumn} {$orderDir}
             LIMIT :perPage OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('perPage', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $items = [];
        while ($row = $stmt->fetch()) {
            $items[] = self::rowToUser($row);
        }

        return new PaginatedResult($items, $totalCount, $page, $perPage);
    }

    public function deleteUser(string $domain, string $userUid, string $adminEmail): void
    {
        $pdo = MysqlConnection::getInstance()->getPdo();
        $username = "{$userUid}@{$domain}";

        $pdo->beginTransaction();
        try {
            // Record mailbox for deferred deletion
            $stmt = $pdo->prepare(
                "INSERT INTO deleted_mailboxes (username, maildir, domain, admin)
                 SELECT username,
                        CONCAT(storagebasedirectory, '/', storagenode, '/', maildir),
                        domain, :admin
                 FROM mailbox
                 WHERE username = :username AND domain = :domain"
            );
            $stmt->execute(['admin' => $adminEmail, 'username' => $username, 'domain' => $domain]);

            // Delete from related tables
            $stmt = $pdo->prepare("DELETE FROM forwardings WHERE address = :u OR forwarding = :u2");
            $stmt->execute(['u' => $username, 'u2' => $username]);

            $stmt = $pdo->prepare("DELETE FROM used_quota WHERE username = :username");
            $stmt->execute(['username' => $username]);

            $stmt = $pdo->prepare("DELETE FROM domain_admins WHERE username = :username");
            $stmt->execute(['username' => $username]);

            $stmt = $pdo->prepare("DELETE FROM mailbox WHERE username = :username AND domain = :domain");
            $stmt->execute(['username' => $username, 'domain' => $domain]);

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw new \RuntimeException("Failed to delete user '{$username}': " . $e->getMessage());
        }
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
