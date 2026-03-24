<?php

declare(strict_types=1);

namespace App\Repositories\Mysql;

use App\Repositories\SearchRepositoryInterface;

class MysqlSearchRepository implements SearchRepositoryInterface
{
    public function search(string $query, array $accountTypes = [], array $statusFilter = [], array $managedDomains = []): array
    {
        $pdo = MysqlConnection::getInstance()->getPdo();
        $likeQuery = '%' . $query . '%';
        $searchAll = empty($accountTypes);

        $domainFilter = '';
        $domainParams = [];
        if (!empty($managedDomains)) {
            $placeholders = [];
            foreach ($managedDomains as $i => $d) {
                $key = "md{$i}";
                $placeholders[] = ":{$key}";
                $domainParams[$key] = $d;
            }
            $domainFilter = ' AND domain IN (' . implode(',', $placeholders) . ')';
        }

        $results = [
            'domains' => [],
            'users' => [],
            'aliases' => [],
            'mailingLists' => [],
            'admins' => [],
        ];

        if ($searchAll || in_array('domain', $accountTypes, true)) {
            $results['domains'] = $this->searchDomains($pdo, $likeQuery, $statusFilter, $domainFilter, $domainParams);
        }

        if ($searchAll || in_array('user', $accountTypes, true)) {
            $results['users'] = $this->searchUsers($pdo, $likeQuery, $statusFilter, $domainFilter, $domainParams);
        }

        if ($searchAll || in_array('alias', $accountTypes, true)) {
            $results['aliases'] = $this->searchAliases($pdo, $likeQuery, $statusFilter, $domainFilter, $domainParams);
        }

        if ($searchAll || in_array('ml', $accountTypes, true)) {
            $results['mailingLists'] = $this->searchMailingLists($pdo, $likeQuery, $statusFilter, $domainFilter, $domainParams);
        }

        if ($searchAll || in_array('admin', $accountTypes, true)) {
            if (empty($managedDomains)) {
                $results['admins'] = $this->searchAdmins($pdo, $likeQuery, $statusFilter);
            }
        }

        return $results;
    }

    private function searchDomains(\PDO $pdo, string $like, array $statusFilter, string $domainFilter, array $domainParams): array
    {
        $where = "(domain LIKE :q OR description LIKE :q)";
        $params = array_merge(['q' => $like], $domainParams);

        if (!empty($domainFilter)) {
            $where .= str_replace('domain', 'domain', $domainFilter);
        }

        $where .= $this->statusClause($statusFilter);

        $stmt = $pdo->prepare("SELECT domain, description, active FROM domain WHERE {$where} ORDER BY domain LIMIT 50");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function searchUsers(\PDO $pdo, string $like, array $statusFilter, string $domainFilter, array $domainParams): array
    {
        $where = "(username LIKE :q OR name LIKE :q)";
        $params = array_merge(['q' => $like], $domainParams);

        if (!empty($domainFilter)) {
            $where .= $domainFilter;
        }

        $where .= $this->statusClause($statusFilter);

        $stmt = $pdo->prepare("SELECT username, name, domain, active FROM mailbox WHERE {$where} ORDER BY username LIMIT 50");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function searchAliases(\PDO $pdo, string $like, array $statusFilter, string $domainFilter, array $domainParams): array
    {
        $where = "(address LIKE :q OR name LIKE :q) AND islist = 1";
        $params = array_merge(['q' => $like], $domainParams);

        if (!empty($domainFilter)) {
            $where .= $domainFilter;
        }

        $where .= $this->statusClause($statusFilter);

        $stmt = $pdo->prepare("SELECT address, name, domain, active FROM alias WHERE {$where} ORDER BY address LIMIT 50");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function searchMailingLists(\PDO $pdo, string $like, array $statusFilter, string $domainFilter, array $domainParams): array
    {
        $where = "(address LIKE :q OR name LIKE :q)";
        $params = array_merge(['q' => $like], $domainParams);

        if (!empty($domainFilter)) {
            $where .= $domainFilter;
        }

        $where .= $this->statusClause($statusFilter);

        $stmt = $pdo->prepare("SELECT address, name, domain, active FROM maillists WHERE {$where} ORDER BY address LIMIT 50");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function searchAdmins(\PDO $pdo, string $like, array $statusFilter): array
    {
        $where = "(username LIKE :q OR name LIKE :q)";
        $params = ['q' => $like];

        $where .= $this->statusClause($statusFilter);

        $stmt = $pdo->prepare("SELECT username, name, active FROM admin WHERE {$where} ORDER BY username LIMIT 50");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function statusClause(array $statusFilter): string
    {
        if (empty($statusFilter)) {
            return '';
        }

        if (in_array('active', $statusFilter, true) && !in_array('disabled', $statusFilter, true)) {
            return ' AND active = 1';
        }

        if (in_array('disabled', $statusFilter, true) && !in_array('active', $statusFilter, true)) {
            return ' AND active = 0';
        }

        return '';
    }
}
