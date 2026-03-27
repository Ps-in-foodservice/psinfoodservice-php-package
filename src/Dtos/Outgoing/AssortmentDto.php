<?php

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Assortment data transfer object
 *
 * Represents an assortment list with its items and pagination information.
 */
class AssortmentDto
{
    /** Assortment unique identifier (GUID) */
    public ?string $Id = null;

    /** Assortment name */
    public ?string $Name = null;

    /** Current page number (for paginated responses) */
    public ?int $PageNumber = null;

    /** Number of items per page (for paginated responses) */
    public ?int $PageSize = null;

    /** Total number of pages (for paginated responses) */
    public ?int $TotalPages = null;

    /** Total number of items in the assortment */
    public ?int $TotalItems = null;

    /**
     * Array of assortment items
     *
     * @var AssortmentItemDto[]|null
     */
    public ?array $Items = null;

    /**
     * Create an AssortmentDto from an array or stdClass object
     *
     * @param array|object $data The data to map from
     * @return self
     */
    public static function fromData($data): self
    {
        $dto = new self();
        $data = is_array($data) ? (object)$data : $data;

        $dto->Id = $data->Id ?? $data->id ?? null;
        $dto->Name = $data->Name ?? $data->name ?? null;
        $dto->PageNumber = $data->PageNumber ?? $data->pageNumber ?? null;
        $dto->PageSize = $data->PageSize ?? $data->pageSize ?? null;
        $dto->TotalPages = $data->TotalPages ?? $data->totalPages ?? null;
        $dto->TotalItems = $data->TotalItems ?? $data->totalItems ?? null;

        // Map items array
        if (isset($data->Items) || isset($data->items)) {
            $items = $data->Items ?? $data->items;
            if (is_array($items)) {
                $dto->Items = array_map(fn($item) => AssortmentItemDto::fromData($item), $items);
            }
        }

        return $dto;
    }
}
