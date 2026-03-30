<?php

declare(strict_types=1);

namespace PSinfoodservice\Helpers;

use Generator;
use PSinfoodservice\Dtos\Outgoing\LookupResultDto;

/**
 * Helper class for paginating through API results
 *
 * Provides convenient methods for iterating through paginated API responses
 * without having to manually handle page numbers and bounds checking.
 *
 * @example
 * ```php
 * use PSinfoodservice\Helpers\Paginator;
 *
 * // Iterate through all changed items across multiple pages
 * $paginator = new Paginator();
 *
 * foreach ($paginator->iterateChangedItems($lookupResult) as $item) {
 *     echo "Processing: {$item->LogisticId}\n";
 * }
 *
 * // Or collect all items at once
 * $allChanged = $paginator->getAllChangedItems($lookupResult);
 * ```
 */
class Paginator
{
    /**
     * Iterate through all changed items in a lookup result
     *
     * @param LookupResultDto $result The lookup result to iterate
     * @return Generator<int, object> Generator yielding each changed item
     */
    public function iterateChangedItems(LookupResultDto $result): Generator
    {
        if ($result->Changed !== null) {
            foreach ($result->Changed as $index => $item) {
                yield $index => $item;
            }
        }
    }

    /**
     * Iterate through all deleted items in a lookup result
     *
     * @param LookupResultDto $result The lookup result to iterate
     * @return Generator<int, object> Generator yielding each deleted item
     */
    public function iterateDeletedItems(LookupResultDto $result): Generator
    {
        if ($result->Deleted !== null) {
            foreach ($result->Deleted as $index => $item) {
                yield $index => $item;
            }
        }
    }

    /**
     * Iterate through all not-changed items in a lookup result
     *
     * @param LookupResultDto $result The lookup result to iterate
     * @return Generator<int, object> Generator yielding each unchanged item
     */
    public function iterateNotChangedItems(LookupResultDto $result): Generator
    {
        if ($result->NotChanged !== null) {
            foreach ($result->NotChanged as $index => $item) {
                yield $index => $item;
            }
        }
    }

    /**
     * Iterate through all not-found identifiers in a lookup result
     *
     * @param LookupResultDto $result The lookup result to iterate
     * @return Generator<int, string> Generator yielding each not-found identifier
     */
    public function iterateNotFoundItems(LookupResultDto $result): Generator
    {
        if ($result->NotFound !== null) {
            foreach ($result->NotFound as $index => $identifier) {
                yield $index => $identifier;
            }
        }
    }

    /**
     * Iterate through ALL items in a lookup result (changed, deleted, not changed)
     *
     * @param LookupResultDto $result The lookup result to iterate
     * @return Generator<int, array{type: string, item: object|string}> Generator yielding items with their type
     */
    public function iterateAllItems(LookupResultDto $result): Generator
    {
        $index = 0;

        if ($result->Changed !== null) {
            foreach ($result->Changed as $item) {
                yield $index++ => ['type' => 'changed', 'item' => $item];
            }
        }

        if ($result->Deleted !== null) {
            foreach ($result->Deleted as $item) {
                yield $index++ => ['type' => 'deleted', 'item' => $item];
            }
        }

        if ($result->NotChanged !== null) {
            foreach ($result->NotChanged as $item) {
                yield $index++ => ['type' => 'not_changed', 'item' => $item];
            }
        }

        if ($result->NotFound !== null) {
            foreach ($result->NotFound as $identifier) {
                yield $index++ => ['type' => 'not_found', 'item' => $identifier];
            }
        }
    }

    /**
     * Get all changed items as an array
     *
     * @param LookupResultDto $result The lookup result
     * @return array Array of all changed items
     */
    public function getAllChangedItems(LookupResultDto $result): array
    {
        return $result->Changed ?? [];
    }

    /**
     * Get all deleted items as an array
     *
     * @param LookupResultDto $result The lookup result
     * @return array Array of all deleted items
     */
    public function getAllDeletedItems(LookupResultDto $result): array
    {
        return $result->Deleted ?? [];
    }

    /**
     * Get all not-changed items as an array
     *
     * @param LookupResultDto $result The lookup result
     * @return array Array of all unchanged items
     */
    public function getAllNotChangedItems(LookupResultDto $result): array
    {
        return $result->NotChanged ?? [];
    }

    /**
     * Get all not-found identifiers as an array
     *
     * @param LookupResultDto $result The lookup result
     * @return array Array of all not-found identifiers
     */
    public function getAllNotFoundItems(LookupResultDto $result): array
    {
        return $result->NotFound ?? [];
    }

    /**
     * Get a summary of the lookup result
     *
     * @param LookupResultDto $result The lookup result
     * @return array{changed: int, deleted: int, not_changed: int, not_found: int, total: int}
     */
    public function getSummary(LookupResultDto $result): array
    {
        $changed = $result->ItemsChanged;
        $deleted = $result->ItemsDeleted;
        $notChanged = $result->ItemsNotChanged;
        $notFound = $result->ItemsNotFound;

        return [
            'changed' => $changed,
            'deleted' => $deleted,
            'not_changed' => $notChanged,
            'not_found' => $notFound,
            'total' => $changed + $deleted + $notChanged + $notFound
        ];
    }

    /**
     * Check if there are more pages available
     *
     * @param LookupResultDto $result The lookup result
     * @return bool True if there are more pages
     */
    public function hasMorePages(LookupResultDto $result): bool
    {
        return $result->PageNumber < $result->TotalPages;
    }

    /**
     * Get the current page number
     *
     * @param LookupResultDto $result The lookup result
     * @return int Current page number (1-based)
     */
    public function getCurrentPage(LookupResultDto $result): int
    {
        return $result->PageNumber;
    }

    /**
     * Get the total number of pages
     *
     * @param LookupResultDto $result The lookup result
     * @return int Total pages
     */
    public function getTotalPages(LookupResultDto $result): int
    {
        return $result->TotalPages;
    }

    /**
     * Get the page size
     *
     * @param LookupResultDto $result The lookup result
     * @return int Items per page
     */
    public function getPageSize(LookupResultDto $result): int
    {
        return $result->PageSize;
    }

    /**
     * Process items in batches with a callback
     *
     * Useful for processing large result sets without loading everything into memory.
     *
     * @param LookupResultDto $result The lookup result
     * @param callable $callback Function to call for each batch. Receives (array $items, string $type)
     * @param int $batchSize Number of items per batch (default: 100)
     * @return int Total number of items processed
     *
     * @example
     * ```php
     * $paginator->processBatches($result, function(array $items, string $type) {
     *     foreach ($items as $item) {
     *         // Process item
     *         echo "{$type}: " . ($item->LogisticId ?? $item) . "\n";
     *     }
     * }, 50);
     * ```
     */
    public function processBatches(LookupResultDto $result, callable $callback, int $batchSize = 100): int
    {
        $processed = 0;

        // Process each category in batches
        $categories = [
            'changed' => $result->Changed ?? [],
            'deleted' => $result->Deleted ?? [],
            'not_changed' => $result->NotChanged ?? [],
            'not_found' => $result->NotFound ?? []
        ];

        foreach ($categories as $type => $items) {
            $chunks = array_chunk($items, $batchSize);
            foreach ($chunks as $chunk) {
                $callback($chunk, $type);
                $processed += count($chunk);
            }
        }

        return $processed;
    }

    /**
     * Filter items by a custom predicate
     *
     * @param LookupResultDto $result The lookup result
     * @param callable $predicate Function that takes an item and returns true to include it
     * @param string $category Which category to filter: 'changed', 'deleted', 'not_changed', or 'all'
     * @return array Filtered items
     *
     * @example
     * ```php
     * // Get only changed items with LogisticId > 1000
     * $filtered = $paginator->filter($result, function($item) {
     *     return $item->LogisticId > 1000;
     * }, 'changed');
     * ```
     */
    public function filter(LookupResultDto $result, callable $predicate, string $category = 'all'): array
    {
        $items = [];

        if ($category === 'all' || $category === 'changed') {
            $items = array_merge($items, array_filter($result->Changed ?? [], $predicate));
        }

        if ($category === 'all' || $category === 'deleted') {
            $items = array_merge($items, array_filter($result->Deleted ?? [], $predicate));
        }

        if ($category === 'all' || $category === 'not_changed') {
            $items = array_merge($items, array_filter($result->NotChanged ?? [], $predicate));
        }

        return array_values($items);
    }
}
