<?php

declare(strict_types=1);

namespace App\Models;

class Alias
{
    public function __construct(
        public readonly string $address,
        public readonly string $domain,
        public readonly string $name = '',
        public readonly string $accessPolicy = 'public',
        public readonly bool $islist = true,
        public readonly bool $active = true,
        public readonly ?string $created = null,
        public readonly ?string $modified = null,
    ) {}
}
