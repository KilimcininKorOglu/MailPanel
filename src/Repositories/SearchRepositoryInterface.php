<?php

declare(strict_types=1);

namespace App\Repositories;

interface SearchRepositoryInterface
{
    /**
     * Searches across domains, users, aliases, mailing lists, and admins.
     *
     * @param string[] $accountTypes Filter by type: domain, user, alias, ml, admin
     * @param string[] $statusFilter Filter by status: active, disabled
     * @param string[] $managedDomains If non-empty, restrict to these domains (RBAC)
     * @return array{domains: array, users: array, aliases: array, mailingLists: array, admins: array}
     */
    public function search(string $query, array $accountTypes = [], array $statusFilter = [], array $managedDomains = []): array;
}
