<?php

declare(strict_types=1);

namespace App\Repositories;

interface DomainOwnershipRepositoryInterface
{
    public function getPendingDomains(): array;

    public function addPendingDomain(string $admin, string $domain, string $verifyCode, int $expireTimestamp): bool;

    public function getVerifyCode(string $domain): ?string;

    public function markVerified(string $domain): bool;

    public function isVerified(string $domain): bool;

    public function deletePendingDomain(string $domain): bool;

    public function verifyDnsTxt(string $domain, string $verifyCode): bool;
}
