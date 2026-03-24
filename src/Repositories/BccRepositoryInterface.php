<?php

declare(strict_types=1);

namespace App\Repositories;

interface BccRepositoryInterface
{
    public function getDomainSenderBcc(string $domain): ?string;

    public function setDomainSenderBcc(string $domain, ?string $bccAddress): bool;

    public function getDomainRecipientBcc(string $domain): ?string;

    public function setDomainRecipientBcc(string $domain, ?string $bccAddress): bool;

    public function getUserSenderBcc(string $email): ?string;

    public function setUserSenderBcc(string $email, ?string $bccAddress): bool;

    public function getUserRecipientBcc(string $email): ?string;

    public function setUserRecipientBcc(string $email, ?string $bccAddress): bool;

    /** @return array<int, array{domain: string, bcc_address: string, active: bool}> */
    public function getAllDomainBcc(?string $domain = null): array;

    /** @return array<int, array{username: string, bcc_address: string, active: bool}> */
    public function getAllUserBcc(?string $domain = null): array;
}
