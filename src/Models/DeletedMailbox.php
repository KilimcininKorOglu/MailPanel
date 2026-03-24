<?php

declare(strict_types=1);

namespace App\Models;

class DeletedMailbox
{
    public function __construct(
        public int $id,
        public string $username,
        public string $maildir,
        public string $domain,
        public string $admin,
        public ?string $deleteDate,
        public ?string $timestamp,
    ) {}

    public static function fromMysqlRow(array $row): self
    {
        return new self(
            id: (int) ($row['id'] ?? 0),
            username: $row['username'] ?? '',
            maildir: $row['maildir'] ?? '',
            domain: $row['domain'] ?? '',
            admin: $row['admin'] ?? '',
            deleteDate: $row['delete_date'] ?? null,
            timestamp: $row['timestamp'] ?? null,
        );
    }
}
