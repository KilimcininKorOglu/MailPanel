<?php

declare(strict_types=1);

namespace App\Api;

use App\Models\Domain;
use App\Repositories\RepositoryFactory;

class DomainApiController
{
    public static function list(): void
    {
        $repo = RepositoryFactory::getDomainRepository();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) ($_GET['perPage'] ?? 50);

        $result = $repo->getDomainsPaginated($page, $perPage);
        ApiResponse::paginated($result, fn(Domain $d) => [
            'domainName' => $d->domainName,
            'description' => $d->description,
            'active' => $d->active,
            'mailboxes' => $d->mailboxes,
            'aliases' => $d->aliases,
            'quota' => $d->quota,
            'maxQuota' => $d->maxQuota,
            'created' => $d->created,
        ]);
    }

    public static function get(string $domain): void
    {
        $d = RepositoryFactory::getDomainRepository()->getDomain($domain);
        if ($d === null) {
            ApiResponse::error('Domain not found', 404);
            return;
        }
        ApiResponse::success((array) $d);
    }

    public static function create(): void
    {
        $data = ApiMiddleware::getJsonBody();
        $domain = Domain::fromFormData($data);

        if (empty($domain->domainName)) {
            ApiResponse::error('domainName is required');
            return;
        }

        $repo = RepositoryFactory::getDomainRepository();
        if ($repo->getDomain($domain->domainName) !== null) {
            ApiResponse::error('Domain already exists', 409);
            return;
        }

        $repo->createDomain($domain);
        ApiResponse::created(['domainName' => $domain->domainName]);
    }

    public static function update(string $domain): void
    {
        $repo = RepositoryFactory::getDomainRepository();
        $existing = $repo->getDomain($domain);
        if ($existing === null) {
            ApiResponse::error('Domain not found', 404);
            return;
        }

        $data = ApiMiddleware::getJsonBody();
        $updated = new Domain(
            domainName: $domain,
            description: $data['description'] ?? $existing->description,
            active: $data['active'] ?? $existing->active,
            maxQuota: $data['maxQuota'] ?? $existing->maxQuota,
            quota: $data['quota'] ?? $existing->quota,
            mailboxes: $data['mailboxes'] ?? $existing->mailboxes,
            aliases: $data['aliases'] ?? $existing->aliases,
            transport: $data['transport'] ?? $existing->transport,
        );

        $repo->updateDomain($updated);
        ApiResponse::success(['message' => 'Domain updated']);
    }

    public static function delete(string $domain): void
    {
        $repo = RepositoryFactory::getDomainRepository();
        if ($repo->getDomain($domain) === null) {
            ApiResponse::error('Domain not found', 404);
            return;
        }

        $repo->deleteDomain($domain, 'api');
        ApiResponse::deleted();
    }
}
