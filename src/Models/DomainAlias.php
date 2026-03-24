<?php

declare(strict_types=1);

namespace App\Models;

class DomainAlias
{
    public function __construct(
        public string $aliasDomain,
        public string $targetDomain,
        public bool $active = true,
        public ?string $created = null,
        public ?string $modified = null,
    ) {}

    public static function fromFormData(array $post): self
    {
        return new self(
            aliasDomain: strtolower(trim($post['aliasDomain'] ?? '')),
            targetDomain: strtolower(trim($post['targetDomain'] ?? '')),
            active: isset($post['active']),
        );
    }

    public static function fromMysqlRow(array $row): self
    {
        return new self(
            aliasDomain: $row['alias_domain'] ?? '',
            targetDomain: $row['target_domain'] ?? '',
            active: (bool) ($row['active'] ?? 1),
            created: $row['created'] ?? null,
            modified: $row['modified'] ?? null,
        );
    }
}
