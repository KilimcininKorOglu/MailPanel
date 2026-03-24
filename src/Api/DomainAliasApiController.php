<?php

declare(strict_types=1);

namespace App\Api;

use App\Models\DomainAlias;
use App\Repositories\RepositoryFactory;

class DomainAliasApiController
{
    public static function list(): void
    {
        $repo = RepositoryFactory::getDomainAliasRepository();
        $result = $repo->getAllAliasesPaginated(
            max(1, (int) ($_GET['page'] ?? 1)),
            (int) ($_GET['perPage'] ?? 50)
        );
        ApiResponse::paginated($result, fn(DomainAlias $a) => [
            'aliasDomain' => $a->aliasDomain,
            'targetDomain' => $a->targetDomain,
            'active' => $a->active,
        ]);
    }

    public static function create(): void
    {
        $data = ApiMiddleware::getJsonBody();
        $aliasDomain = $data['aliasDomain'] ?? '';
        $targetDomain = $data['targetDomain'] ?? '';

        if ($aliasDomain === '' || $targetDomain === '') {
            ApiResponse::error('aliasDomain and targetDomain are required');
            return;
        }

        $alias = new DomainAlias($aliasDomain, $targetDomain, true);
        RepositoryFactory::getDomainAliasRepository()->createAlias($alias);
        ApiResponse::created(['aliasDomain' => $aliasDomain]);
    }

    public static function delete(string $aliasDomain): void
    {
        RepositoryFactory::getDomainAliasRepository()->deleteAlias($aliasDomain);
        ApiResponse::deleted();
    }
}
