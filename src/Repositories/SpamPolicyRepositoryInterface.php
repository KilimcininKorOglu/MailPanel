<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\SpamPolicy;

interface SpamPolicyRepositoryInterface
{
    /** @param string $account @. = global, @domain.com = per-domain, user@domain.com = per-user */
    public function getPolicy(string $account): ?SpamPolicy;

    public function createOrUpdatePolicy(string $account, SpamPolicy $policy): bool;

    public function deletePolicy(string $account): bool;

    /** @return array<int, array{account: string, policy: SpamPolicy}> */
    public function listPolicies(?string $domain = null): array;
}
