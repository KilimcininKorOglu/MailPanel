<?php

declare(strict_types=1);

namespace App\Repositories;

interface DomainRepositoryInterface
{
    /**
     * Returns array of domain info arrays, each containing:
     *   'domainName' => string
     *   'accountStatus' => string ('active' or 'disabled')
     *   'domainCurrentUserNumber' => string|int
     *
     * @return array<int, array<string, string>>
     */
    public function getDomains(): array;
}
