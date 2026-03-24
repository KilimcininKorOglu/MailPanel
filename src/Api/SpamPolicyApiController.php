<?php

declare(strict_types=1);

namespace App\Api;

use App\Models\SpamPolicy;
use App\Repositories\RepositoryFactory;

class SpamPolicyApiController
{
    public static function get(string $account): void
    {
        $policy = RepositoryFactory::getSpamPolicyRepository()->getPolicy($account);
        if ($policy === null) {
            ApiResponse::error('No policy found for account', 404);
            return;
        }
        ApiResponse::success((array) $policy);
    }

    public static function update(string $account): void
    {
        $data = ApiMiddleware::getJsonBody();
        $policy = SpamPolicy::fromFormData($data);
        RepositoryFactory::getSpamPolicyRepository()->createOrUpdatePolicy($account, $policy);
        ApiResponse::success(['message' => 'Spam policy updated']);
    }
}
