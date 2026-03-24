<?php

declare(strict_types=1);

namespace App\Api;

use App\Repositories\RepositoryFactory;

class WhiteBlacklistApiController
{
    public static function get(string $account): void
    {
        $repo = RepositoryFactory::getWhiteBlacklistRepository();
        ApiResponse::success([
            'inbound' => $repo->getInboundList($account),
            'outbound' => $repo->getOutboundList($account),
        ]);
    }

    public static function update(string $account): void
    {
        $data = ApiMiddleware::getJsonBody();
        $repo = RepositoryFactory::getWhiteBlacklistRepository();

        $sender = $data['sender'] ?? '';
        $wb = $data['wb'] ?? 'W';
        $direction = $data['direction'] ?? 'inbound';

        if ($sender === '') {
            ApiResponse::error('sender is required');
            return;
        }

        if ($direction === 'outbound') {
            $repo->addOutboundEntry($account, $sender, $wb);
        } else {
            $repo->addInboundEntry($account, $sender, $wb);
        }

        ApiResponse::success(['message' => 'Entry added']);
    }

    public static function delete(string $account): void
    {
        $data = ApiMiddleware::getJsonBody();
        $repo = RepositoryFactory::getWhiteBlacklistRepository();

        $sender = $data['sender'] ?? '';
        $direction = $data['direction'] ?? 'inbound';

        if ($sender === '') {
            ApiResponse::error('sender is required');
            return;
        }

        if ($direction === 'outbound') {
            $repo->removeOutboundEntry($account, $sender);
        } else {
            $repo->removeInboundEntry($account, $sender);
        }

        ApiResponse::deleted();
    }
}
