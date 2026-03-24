<?php

declare(strict_types=1);

namespace App\Repositories;

interface RelayRepositoryInterface
{
    public function getRelayhost(string $account): ?string;

    public function setRelayhost(string $account, ?string $relayhost): bool;

    /** @return array<int, array{account: string, relayhost: string}> */
    public function getAllRelayhosts(?string $domain = null): array;

    public function deleteRelayhost(string $account): bool;
}
