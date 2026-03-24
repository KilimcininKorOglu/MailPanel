<?php

declare(strict_types=1);

namespace App\Api;

use App\Models\MailingList;
use App\Repositories\RepositoryFactory;

class MailingListApiController
{
    public static function list(): void
    {
        $repo = RepositoryFactory::getMailingListRepository();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) ($_GET['perPage'] ?? 50);
        $domain = $_GET['domain'] ?? null;

        $result = $repo->getMailingListsPaginated($page, $perPage, $domain);
        ApiResponse::paginated($result, fn(MailingList $ml) => [
            'address' => $ml->address,
            'domain' => $ml->domain,
            'name' => $ml->name,
            'accessPolicy' => $ml->accessPolicy,
            'active' => $ml->active,
        ]);
    }

    public static function get(string $address): void
    {
        $repo = RepositoryFactory::getMailingListRepository();
        $ml = $repo->getMailingList($address);
        if ($ml === null) {
            ApiResponse::error('Mailing list not found', 404);
            return;
        }

        $owners = $repo->getOwners($address);
        ApiResponse::success(array_merge((array) $ml, ['owners' => $owners]));
    }

    public static function create(): void
    {
        $data = ApiMiddleware::getJsonBody();
        $address = $data['address'] ?? '';
        $domain = $data['domain'] ?? '';

        if ($address === '' || $domain === '') {
            ApiResponse::error('address and domain are required');
            return;
        }

        $repo = RepositoryFactory::getMailingListRepository();
        if ($repo->getMailingList($address) !== null) {
            ApiResponse::error('Mailing list already exists', 409);
            return;
        }

        $repo->createMailingList(
            $address, $domain,
            $data['name'] ?? '',
            $data['accessPolicy'] ?? 'public',
            (int) ($data['maxMsgSize'] ?? 0),
            (int) ($data['maxMembers'] ?? 0),
        );
        ApiResponse::created(['address' => $address]);
    }

    public static function update(string $address): void
    {
        $repo = RepositoryFactory::getMailingListRepository();
        $ml = $repo->getMailingList($address);
        if ($ml === null) {
            ApiResponse::error('Mailing list not found', 404);
            return;
        }

        $data = ApiMiddleware::getJsonBody();
        $repo->updateMailingList(
            $address,
            $data['name'] ?? $ml->name,
            $data['accessPolicy'] ?? $ml->accessPolicy,
            (int) ($data['maxMsgSize'] ?? $ml->maxMsgSize),
            (int) ($data['maxMembers'] ?? $ml->maxMembers),
            $data['active'] ?? $ml->active,
        );
        ApiResponse::success(['message' => 'Mailing list updated']);
    }

    public static function delete(string $address): void
    {
        $repo = RepositoryFactory::getMailingListRepository();
        if ($repo->getMailingList($address) === null) {
            ApiResponse::error('Mailing list not found', 404);
            return;
        }

        $repo->deleteMailingList($address);
        ApiResponse::deleted();
    }
}
