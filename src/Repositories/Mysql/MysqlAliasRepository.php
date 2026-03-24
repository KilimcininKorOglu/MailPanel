<?php

declare(strict_types=1);

namespace App\Repositories\Mysql;

use App\Models\Alias;
use App\Models\PaginatedResult;
use App\Repositories\AliasRepositoryInterface;

class MysqlAliasRepository implements AliasRepositoryInterface
{
    public function getAliasesPaginated(int $page, int $perPage, ?string $domain = null): PaginatedResult
    {
        $pdo = MysqlConnection::getInstance()->getPdo();
        $offset = ($page - 1) * $perPage;

        $where = "WHERE islist = 1";
        $params = [];
        if ($domain !== null) {
            $where .= " AND domain = :domain";
            $params['domain'] = $domain;
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM alias {$where}");
        $countStmt->execute($params);
        $totalCount = (int) $countStmt->fetch()['total'];

        $stmt = $pdo->prepare(
            "SELECT address, domain, name, accesspolicy, islist, active, created, modified
             FROM alias {$where}
             ORDER BY address
             LIMIT :perPage OFFSET :offset"
        );
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue('perPage', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $items = [];
        while ($row = $stmt->fetch()) {
            $items[] = $this->rowToAlias($row);
        }

        return new PaginatedResult($items, $totalCount, $page, $perPage);
    }

    public function getAlias(string $address): ?Alias
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare(
            "SELECT address, domain, name, accesspolicy, islist, active, created, modified
             FROM alias
             WHERE address = :address AND islist = 1
             LIMIT 1"
        );
        $stmt->execute(['address' => $address]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return $this->rowToAlias($row);
    }

    public function createAlias(string $address, string $domain, string $name, array $members, string $accessPolicy): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $pdo->beginTransaction();
        try {
            $goto = !empty($members) ? implode(',', $members) : '';

            $stmt = $pdo->prepare(
                "INSERT INTO alias (address, domain, name, accesspolicy, goto, islist, active, created)
                 VALUES (:address, :domain, :name, :accesspolicy, :goto, 1, 1, NOW())"
            );
            $stmt->execute([
                'address' => $address,
                'domain' => $domain,
                'name' => $name,
                'accesspolicy' => $accessPolicy,
                'goto' => $goto,
            ]);

            foreach ($members as $member) {
                $member = trim($member);
                if ($member === '') {
                    continue;
                }
                $destDomain = str_contains($member, '@') ? explode('@', $member, 2)[1] : $domain;
                $fwdStmt = $pdo->prepare(
                    "INSERT INTO forwardings (address, forwarding, domain, dest_domain, is_list, active)
                     VALUES (:address, :forwarding, :domain, :destDomain, 1, 1)"
                );
                $fwdStmt->execute([
                    'address' => $address,
                    'forwarding' => $member,
                    'domain' => $domain,
                    'destDomain' => $destDomain,
                ]);
            }

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function updateAlias(string $address, string $name, array $members, string $accessPolicy, bool $active): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $pdo->beginTransaction();
        try {
            $goto = !empty($members) ? implode(',', $members) : '';

            $stmt = $pdo->prepare(
                "UPDATE alias SET name = :name, accesspolicy = :accesspolicy, goto = :goto,
                 active = :active, modified = NOW()
                 WHERE address = :address AND islist = 1"
            );
            $stmt->execute([
                'name' => $name,
                'accesspolicy' => $accessPolicy,
                'goto' => $goto,
                'active' => $active ? 1 : 0,
                'address' => $address,
            ]);

            $alias = $this->getAlias($address);
            $domain = $alias ? $alias->domain : explode('@', $address, 2)[1];

            $pdo->prepare("DELETE FROM forwardings WHERE address = :address AND is_list = 1")
                ->execute(['address' => $address]);

            foreach ($members as $member) {
                $member = trim($member);
                if ($member === '') {
                    continue;
                }
                $destDomain = str_contains($member, '@') ? explode('@', $member, 2)[1] : $domain;
                $fwdStmt = $pdo->prepare(
                    "INSERT INTO forwardings (address, forwarding, domain, dest_domain, is_list, active)
                     VALUES (:address, :forwarding, :domain, :destDomain, 1, 1)"
                );
                $fwdStmt->execute([
                    'address' => $address,
                    'forwarding' => $member,
                    'domain' => $domain,
                    'destDomain' => $destDomain,
                ]);
            }

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function deleteAlias(string $address): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $pdo->beginTransaction();
        try {
            $pdo->prepare("DELETE FROM forwardings WHERE address = :address AND is_list = 1")
                ->execute(['address' => $address]);
            $pdo->prepare("DELETE FROM moderators WHERE address = :address")
                ->execute(['address' => $address]);
            $pdo->prepare("DELETE FROM alias WHERE address = :address AND islist = 1")
                ->execute(['address' => $address]);

            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function getAliasMembers(string $address): array
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare(
            "SELECT forwarding FROM forwardings
             WHERE address = :address AND is_list = 1 AND active = 1
             ORDER BY forwarding"
        );
        $stmt->execute(['address' => $address]);

        $members = [];
        while ($row = $stmt->fetch()) {
            $members[] = $row['forwarding'];
        }

        return $members;
    }

    public function addAliasMember(string $address, string $member): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $domain = explode('@', $address, 2)[1];
        $destDomain = str_contains($member, '@') ? explode('@', $member, 2)[1] : $domain;

        $stmt = $pdo->prepare(
            "INSERT IGNORE INTO forwardings (address, forwarding, domain, dest_domain, is_list, active)
             VALUES (:address, :forwarding, :domain, :destDomain, 1, 1)"
        );
        $stmt->execute([
            'address' => $address,
            'forwarding' => $member,
            'domain' => $domain,
            'destDomain' => $destDomain,
        ]);

        $this->updateGotoField($address);
        return true;
    }

    public function removeAliasMember(string $address, string $member): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare(
            "DELETE FROM forwardings WHERE address = :address AND forwarding = :forwarding AND is_list = 1"
        );
        $stmt->execute(['address' => $address, 'forwarding' => $member]);

        $this->updateGotoField($address);
        return true;
    }

    public function getModerators(string $address): array
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare(
            "SELECT moderator FROM moderators WHERE address = :address ORDER BY moderator"
        );
        $stmt->execute(['address' => $address]);

        $moderators = [];
        while ($row = $stmt->fetch()) {
            $moderators[] = $row['moderator'];
        }

        return $moderators;
    }

    public function setModerators(string $address, array $moderators): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $domain = explode('@', $address, 2)[1];

        $pdo->prepare("DELETE FROM moderators WHERE address = :address")
            ->execute(['address' => $address]);

        $stmt = $pdo->prepare(
            "INSERT INTO moderators (address, moderator, domain, dest_domain)
             VALUES (:address, :moderator, :domain, :destDomain)"
        );

        foreach ($moderators as $moderator) {
            $moderator = trim($moderator);
            if ($moderator === '') {
                continue;
            }
            $destDomain = str_contains($moderator, '@') ? explode('@', $moderator, 2)[1] : $domain;
            $stmt->execute([
                'address' => $address,
                'moderator' => $moderator,
                'domain' => $domain,
                'destDomain' => $destDomain,
            ]);
        }

        return true;
    }

    public function getUserAliases(string $email): array
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare(
            "SELECT address FROM forwardings
             WHERE forwarding = :email AND is_alias = 1 AND active = 1
             ORDER BY address"
        );
        $stmt->execute(['email' => $email]);

        $aliases = [];
        while ($row = $stmt->fetch()) {
            $aliases[] = $row['address'];
        }

        return $aliases;
    }

    public function addUserAlias(string $email, string $aliasAddress): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $domain = explode('@', $email, 2)[1];
        $aliasDomain = str_contains($aliasAddress, '@') ? explode('@', $aliasAddress, 2)[1] : $domain;

        $stmt = $pdo->prepare(
            "INSERT IGNORE INTO forwardings (address, forwarding, domain, dest_domain, is_alias, active)
             VALUES (:address, :forwarding, :domain, :destDomain, 1, 1)"
        );
        $stmt->execute([
            'address' => $aliasAddress,
            'forwarding' => $email,
            'domain' => $aliasDomain,
            'destDomain' => $domain,
        ]);

        return true;
    }

    public function removeUserAlias(string $email, string $aliasAddress): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare(
            "DELETE FROM forwardings WHERE address = :address AND forwarding = :email AND is_alias = 1"
        );
        $stmt->execute(['address' => $aliasAddress, 'email' => $email]);

        return true;
    }

    public function getCatchall(string $domain): ?string
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $catchallAddress = '@' . $domain;
        $stmt = $pdo->prepare(
            "SELECT goto FROM alias WHERE address = :address LIMIT 1"
        );
        $stmt->execute(['address' => $catchallAddress]);

        $row = $stmt->fetch();
        if ($row === false || empty($row['goto'])) {
            return null;
        }

        return $row['goto'];
    }

    public function setCatchall(string $domain, ?string $targetEmail): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $catchallAddress = '@' . $domain;

        if ($targetEmail === null || $targetEmail === '') {
            $pdo->prepare("DELETE FROM alias WHERE address = :address")
                ->execute(['address' => $catchallAddress]);
        } else {
            $stmt = $pdo->prepare("SELECT 1 FROM alias WHERE address = :address LIMIT 1");
            $stmt->execute(['address' => $catchallAddress]);

            if ($stmt->fetch() !== false) {
                $pdo->prepare(
                    "UPDATE alias SET goto = :goto, modified = NOW() WHERE address = :address"
                )->execute(['goto' => $targetEmail, 'address' => $catchallAddress]);
            } else {
                $pdo->prepare(
                    "INSERT INTO alias (address, goto, domain, active, created)
                     VALUES (:address, :goto, :domain, 1, NOW())"
                )->execute([
                    'address' => $catchallAddress,
                    'goto' => $targetEmail,
                    'domain' => $domain,
                ]);
            }
        }

        return true;
    }

    public function enableDisableAlias(string $address, bool $active): bool
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->prepare(
            "UPDATE alias SET active = :active, modified = NOW() WHERE address = :address AND islist = 1"
        );
        $stmt->execute(['active' => $active ? 1 : 0, 'address' => $address]);

        return true;
    }

    private function rowToAlias(array $row): Alias
    {
        return new Alias(
            address: $row['address'],
            domain: $row['domain'],
            name: $row['name'] ?? '',
            accessPolicy: $row['accesspolicy'] ?? 'public',
            islist: (bool) ($row['islist'] ?? true),
            active: (bool) ($row['active'] ?? true),
            created: $row['created'] ?? null,
            modified: $row['modified'] ?? null,
        );
    }

    private function updateGotoField(string $address): void
    {
        $members = $this->getAliasMembers($address);
        $goto = implode(',', $members);

        $pdo = MysqlConnection::getInstance()->getPdo();
        $pdo->prepare("UPDATE alias SET goto = :goto, modified = NOW() WHERE address = :address")
            ->execute(['goto' => $goto, 'address' => $address]);
    }
}
