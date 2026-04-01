<?php

declare(strict_types=1);

namespace App\Api;

use App\Repositories\RepositoryFactory;

class ThrottleApiController
{
    public static function get(string $account): void
    {
        ApiMiddleware::requireGlobalKey();
        $settings = RepositoryFactory::getIredapdRepository()->getThrottleSettings($account);
        ApiResponse::success(['account' => $account, 'settings' => $settings]);
    }

    public static function update(string $account): void
    {
        ApiMiddleware::requireGlobalKey();
        ApiMiddleware::requireWriteAccess();
        $data = ApiMiddleware::getJsonBody();
        $repo = RepositoryFactory::getIredapdRepository();

        $repo->setThrottleSettings(
            $account,
            $data['kind'] ?? 'outbound',
            (int) ($data['period'] ?? 3600),
            (int) ($data['maxMsgs'] ?? 0),
            (int) ($data['maxQuota'] ?? 0),
            (int) ($data['msgSize'] ?? 0),
        );

        ApiResponse::success(['message' => 'Throttle settings updated']);
    }
}
