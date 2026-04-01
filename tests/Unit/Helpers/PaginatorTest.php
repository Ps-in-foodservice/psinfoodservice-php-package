<?php

namespace PSinfoodservice\Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use PSinfoodservice\Dtos\Outgoing\LookupResultDto;
use PSinfoodservice\Helpers\Paginator;

class PaginatorTest extends TestCase
{
    private Paginator $paginator;

    protected function setUp(): void
    {
        $this->paginator = new Paginator();
    }

    private function createLookupResult(array $data = []): LookupResultDto
    {
        $result = new LookupResultDto();
        $result->PageNumber = $data['pageNumber'] ?? 1;
        $result->PageSize = $data['pageSize'] ?? 100;
        $result->TotalPages = $data['totalPages'] ?? 1;
        $result->ItemsChanged = $data['itemsChanged'] ?? 0;
        $result->ItemsDeleted = $data['itemsDeleted'] ?? 0;
        $result->ItemsNotChanged = $data['itemsNotChanged'] ?? 0;
        $result->ItemsNotFound = $data['itemsNotFound'] ?? 0;
        $result->Changed = $data['changed'] ?? null;
        $result->Deleted = $data['deleted'] ?? null;
        $result->NotChanged = $data['notChanged'] ?? null;
        $result->NotFound = $data['notFound'] ?? null;
        return $result;
    }

    private function createMockItem(int $id): object
    {
        $item = new \stdClass();
        $item->LogisticId = $id;
        $item->GTIN = '123456789012' . $id;
        return $item;
    }

    public function test_iterate_changed_items_yields_all_items()
    {
        $result = $this->createLookupResult([
            'changed' => [
                $this->createMockItem(1),
                $this->createMockItem(2),
                $this->createMockItem(3)
            ],
            'itemsChanged' => 3
        ]);

        $items = iterator_to_array($this->paginator->iterateChangedItems($result));

        $this->assertCount(3, $items);
        $this->assertSame(1, $items[0]->LogisticId);
        $this->assertSame(2, $items[1]->LogisticId);
        $this->assertSame(3, $items[2]->LogisticId);
    }

    public function test_iterate_changed_items_handles_null()
    {
        $result = $this->createLookupResult(['changed' => null]);
        $items = iterator_to_array($this->paginator->iterateChangedItems($result));
        $this->assertEmpty($items);
    }

    public function test_iterate_deleted_items_yields_all_items()
    {
        $result = $this->createLookupResult([
            'deleted' => [
                $this->createMockItem(10),
                $this->createMockItem(20)
            ],
            'itemsDeleted' => 2
        ]);

        $items = iterator_to_array($this->paginator->iterateDeletedItems($result));
        $this->assertCount(2, $items);
        $this->assertSame(10, $items[0]->LogisticId);
    }

    public function test_iterate_not_found_items_yields_identifiers()
    {
        $result = $this->createLookupResult([
            'notFound' => ['1234567890123', '9876543210987'],
            'itemsNotFound' => 2
        ]);

        $items = iterator_to_array($this->paginator->iterateNotFoundItems($result));
        $this->assertCount(2, $items);
        $this->assertSame('1234567890123', $items[0]);
    }

    public function test_get_summary_returns_correct_counts()
    {
        $result = $this->createLookupResult([
            'itemsChanged' => 5,
            'itemsDeleted' => 3,
            'itemsNotChanged' => 10,
            'itemsNotFound' => 2
        ]);

        $summary = $this->paginator->getSummary($result);

        $this->assertSame(5, $summary['changed']);
        $this->assertSame(3, $summary['deleted']);
        $this->assertSame(10, $summary['not_changed']);
        $this->assertSame(2, $summary['not_found']);
        $this->assertSame(20, $summary['total']);
    }

    public function test_has_more_pages_returns_true_when_not_last_page()
    {
        $result = $this->createLookupResult(['pageNumber' => 1, 'totalPages' => 5]);
        $this->assertTrue($this->paginator->hasMorePages($result));
    }

    public function test_has_more_pages_returns_false_on_last_page()
    {
        $result = $this->createLookupResult(['pageNumber' => 5, 'totalPages' => 5]);
        $this->assertFalse($this->paginator->hasMorePages($result));
    }

    public function test_get_current_page_returns_page_number()
    {
        $result = $this->createLookupResult(['pageNumber' => 3]);
        $this->assertSame(3, $this->paginator->getCurrentPage($result));
    }

    public function test_get_total_pages_returns_total()
    {
        $result = $this->createLookupResult(['totalPages' => 10]);
        $this->assertSame(10, $this->paginator->getTotalPages($result));
    }

    public function test_get_page_size_returns_size()
    {
        $result = $this->createLookupResult(['pageSize' => 50]);
        $this->assertSame(50, $this->paginator->getPageSize($result));
    }

    public function test_get_all_changed_items_returns_array()
    {
        $result = $this->createLookupResult([
            'changed' => [$this->createMockItem(1), $this->createMockItem(2)]
        ]);
        $items = $this->paginator->getAllChangedItems($result);
        $this->assertIsArray($items);
        $this->assertCount(2, $items);
    }

    public function test_get_all_changed_items_returns_empty_array_for_null()
    {
        $result = $this->createLookupResult(['changed' => null]);
        $items = $this->paginator->getAllChangedItems($result);
        $this->assertIsArray($items);
        $this->assertEmpty($items);
    }

    public function test_filter_returns_filtered_items()
    {
        $result = $this->createLookupResult([
            'changed' => [
                $this->createMockItem(100),
                $this->createMockItem(500),
                $this->createMockItem(1000)
            ]
        ]);

        $filtered = $this->paginator->filter($result, function($item) {
            return $item->LogisticId >= 500;
        }, 'changed');

        $this->assertCount(2, $filtered);
    }

    public function test_filter_returns_empty_array_when_no_matches()
    {
        $result = $this->createLookupResult([
            'changed' => [$this->createMockItem(1), $this->createMockItem(2)]
        ]);

        $filtered = $this->paginator->filter($result, function($item) {
            return $item->LogisticId > 1000;
        }, 'changed');

        $this->assertEmpty($filtered);
    }

    public function test_process_batches_calls_callback()
    {
        $result = $this->createLookupResult([
            'changed' => [$this->createMockItem(1), $this->createMockItem(2), $this->createMockItem(3)],
            'deleted' => [$this->createMockItem(4)]
        ]);

        $callCount = 0;
        $total = $this->paginator->processBatches($result, function($items, $type) use (&$callCount) {
            $callCount++;
        }, 2);

        $this->assertSame(4, $total);
        $this->assertGreaterThan(0, $callCount);
    }
}
