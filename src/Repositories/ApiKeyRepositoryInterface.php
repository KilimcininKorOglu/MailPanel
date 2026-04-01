<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ApiKey;

interface ApiKeyRepositoryInterface
{
    /**
     * Finds an active API key by its key string.
     */
    public function findByKey(string $apiKey): ?ApiKey;

    /**
     * Ensures the api_keys table exists in the iredadmin database.
     */
    public function ensureTableExists(): void;
}
