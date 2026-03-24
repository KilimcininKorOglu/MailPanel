<?php

declare(strict_types=1);

namespace App\Repositories\Pgsql;

use App\Repositories\SearchRepositoryInterface;

class PgsqlSearchRepository implements SearchRepositoryInterface
{
    public function search(string $query, array $accountTypes = [], array $statusFilter = [], array $managedDomains = []): array
    {
        $pdo = PgsqlConnection::getInstance()->getPdo();
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
            $results['domains'] = $this->searchTable($pdo, 'domain', 'domain', ['domain', 'description'], $likeQuery, $statusFilter, $domainFilter, $domainParams);
        }

        if ($searchAll || in_array('user', $accountTypes, true)) {
            $results['users'] = $this->searchTable($pdo, 'mailbox', 'username', ['username', 'name'], $likeQuery, $statusFilter, $domainFilter, $domainParams);
        }

        if ($searchAll || in_array('alias', $accountTypes, true)) {
            $results['aliases'] = $this->searchTable($pdo, 'alias', 'address', ['address', 'name'], $likeQuery, $statusFilter, $domainFilter, $domainParams, 'AND islist = 1');
        }

        if ($searchAll || in_array('ml', $accountTypes, true)) {
            $results['mailingLists'] = $this->searchTable($pdo, 'maillists', 'address', ['address', 'name'], $likeQuery, $statusFilter, $domainFilter, $domainParams);
        }

        if ($searchAll || in_array('admin', $accountTypes, true)) {
            if (empty($managedDomains)) {
                $results['admins'] = $this->searchTable($pdo, 'admin', 'username', ['username', 'name'], $likeQuery, $statusFilter, '', []);
            }
        }

        return $results;
    }

    private function searchTable(\PDO $pdo, string $table, string $orderCol, array $searchCols, string $like, array $statusFilter, string $domainFilter, array $domainParams, string $extraWhere = ''): array
    {
        $conditions = [];
        foreach ($searchCols as $col) {
            $conditions[] = "{$col} ILIKE :q";
        }
        $where = '(' . implode(' OR ', $conditions) . ')';
        $params = array_merge(['q' => $like], $domainParams);

        if ($extraWhere !== '') {
            $where .= ' ' . $extraWhere;
        }

        if (!empty($domainFilter)) {
            $where .= $domainFilter;
        }

        if (in_array('active', $statusFilter, true) && !in_array('disabled', $statusFilter, true)) {
            $where .= ' AND active = 1';
        } elseif (in_array('disabled', $statusFilter, true) && !in_array('active', $statusFilter, true)) {
            $where .= ' AND active = 0';
        }

        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE {$where} ORDER BY {$orderCol} LIMIT 50");
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
