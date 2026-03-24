<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\PaginatedResult;
use PHPUnit\Framework\TestCase;

class PaginatedResultTest extends TestCase
{
    public function testTotalPages(): void
    {
        $result = new PaginatedResult([], 100, 1, 25);
        $this->assertSame(4, $result->totalPages());
    }

    public function testTotalPagesRoundsUp(): void
    {
        $result = new PaginatedResult([], 101, 1, 25);
        $this->assertSame(5, $result->totalPages());
    }

    public function testTotalPagesWithZeroItems(): void
    {
        $result = new PaginatedResult([], 0, 1, 25);
        $this->assertSame(0, $result->totalPages());
    }

    public function testTotalPagesWithZeroPerPage(): void
    {
        $result = new PaginatedResult([], 10, 1, 0);
        $this->assertSame(1, $result->totalPages());
    }

    public function testHasPreviousPageOnFirstPage(): void
    {
        $result = new PaginatedResult([], 100, 1, 25);
        $this->assertFalse($result->hasPreviousPage());
    }

    public function testHasPreviousPageOnSecondPage(): void
    {
        $result = new PaginatedResult([], 100, 2, 25);
        $this->assertTrue($result->hasPreviousPage());
    }

    public function testHasNextPageOnLastPage(): void
    {
        $result = new PaginatedResult([], 100, 4, 25);
        $this->assertFalse($result->hasNextPage());
    }

    public function testHasNextPageOnFirstPage(): void
    {
        $result = new PaginatedResult([], 100, 1, 25);
        $this->assertTrue($result->hasNextPage());
    }

    public function testHasNextPageSinglePage(): void
    {
        $result = new PaginatedResult([], 10, 1, 50);
        $this->assertFalse($result->hasNextPage());
    }

    public function testItemsPreserved(): void
    {
        $items = ['a', 'b', 'c'];
        $result = new PaginatedResult($items, 3, 1, 10);

        $this->assertSame($items, $result->items);
        $this->assertSame(3, $result->totalCount);
        $this->assertSame(1, $result->currentPage);
        $this->assertSame(10, $result->perPage);
    }
}
