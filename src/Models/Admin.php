<?php

declare(strict_types=1);

namespace App\Models;

class Admin
{
    public function __construct(
        public string $username,
        public string $name = '',
        public bool $active = true,
        public bool $isGlobalAdmin = false,
        public bool $isMailboxAdmin = false,
        public ?string $created = null,
        public ?string $passwordLastChange = null,
    ) {}

    public static function fromFormData(array $post): self
    {
        return new self(
            username: strtolower(trim($post['username'] ?? '')),
            name: trim($post['name'] ?? ''),
            active: isset($post['active']),
            isGlobalAdmin: isset($post['isGlobalAdmin']),
        );
    }

    public static function fromMysqlRow(array $row, bool $isMailboxAdmin = false): self
    {
        return new self(
            username: $row['username'] ?? '',
            name: $row['name'] ?? '',
            active: (bool) ($row['active'] ?? 1),
            isGlobalAdmin: (bool) ($row['isGlobalAdmin'] ?? 0),
            isMailboxAdmin: $isMailboxAdmin,
            created: $row['created'] ?? null,
            passwordLastChange: $row['passwordlastchange'] ?? null,
        );
    }

    public static function fromLdapEntry(array $entry, bool $isMailboxAdmin = false): self
    {
        return new self(
            username: $entry['mail'] ?? '',
            name: $entry['cn'] ?? '',
            active: ($entry['accountStatus'] ?? 'active') === 'active',
            isGlobalAdmin: ($entry['domainGlobalAdmin'] ?? '') === 'yes',
            isMailboxAdmin: $isMailboxAdmin,
        );
    }
}
