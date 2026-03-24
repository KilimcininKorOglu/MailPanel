<?php

declare(strict_types=1);

namespace App\Repositories;

interface IredapdRepositoryInterface
{
    public function getThrottleSettings(string $account): array;
    public function setThrottleSettings(string $account, string $kind, int $period, int $maxMsgs, int $maxQuota, int $msgSize): void;
    public function getGreylistSettings(string $account): array;
    public function setGreylistEnabled(string $account, bool $enabled): void;
    /** @return string[] */
    public function getWhitelistedSenders(string $account): array;
    /** @param string[] $senders */
    public function setWhitelistedSenders(string $account, array $senders): void;
}
