<?php

declare(strict_types=1);

namespace App\Api;

use App\Repositories\RepositoryFactory;

class GreylistApiController
{
    public static function get(string $account): void
    {
        $repo = RepositoryFactory::getIredapdRepository();
        $settings = $repo->getGreylistSettings($account);
        $whitelisted = $repo->getWhitelistedSenders($account);

        ApiResponse::success([
            'account' => $account,
            'settings' => $settings,
            'whitelistedSenders' => $whitelisted,
        ]);
    }

    public static function update(string $account): void
    {
        $data = ApiMiddleware::getJsonBody();
        $repo = RepositoryFactory::getIredapdRepository();

        if (isset($data['enabled'])) {
            $repo->setGreylistEnabled($account, (bool) $data['enabled']);
        }

        if (isset($data['whitelistedSenders'])) {
            $repo->setWhitelistedSenders($account, $data['whitelistedSenders']);
        }

        ApiResponse::success(['message' => 'Greylist settings updated']);
    }
}
