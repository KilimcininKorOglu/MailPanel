<?php

declare(strict_types=1);

namespace App\Repositories\Mysql;

use App\Repositories\DomainRepositoryInterface;

class MysqlDomainRepository implements DomainRepositoryInterface
{
    public function getDomains(): array
    {
        $pdo = MysqlConnection::getInstance()->getPdo();

        $stmt = $pdo->query(
            "SELECT d.domain AS domainName,
                    d.active,
                    COUNT(m.username) AS userCount
             FROM domain d
             LEFT JOIN mailbox m ON m.domain = d.domain
             GROUP BY d.domain, d.active
             ORDER BY d.domain"
        );

        $domains = [];
        while ($row = $stmt->fetch()) {
            $domains[] = [
                'domainName' => $row['domainName'],
                'accountStatus' => $row['active'] ? 'active' : 'disabled',
                'domainCurrentUserNumber' => (string) $row['userCount'],
            ];
        }

        return $domains;
    }
}
