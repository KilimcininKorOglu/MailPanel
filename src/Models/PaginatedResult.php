<?php

declare(strict_types=1);

namespace App\Models;

class PaginatedResult
{
    public function __construct(
        public array $items,
        public int $totalCount,
        public int $currentPage,
        public int $perPage,
    ) {}

    public function totalPages(): int
    {
        if ($this->perPage <= 0) {
            return 1;
        }
        return (int) ceil($this->totalCount / $this->perPage);
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages();
    }
}
