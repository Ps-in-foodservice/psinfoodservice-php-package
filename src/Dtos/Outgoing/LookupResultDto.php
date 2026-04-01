<?php

declare(strict_types=1);

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Lookup result data transfer object (also known as RequestStatusListDto)
 *
 * Represents the result of a product lookup operation, categorizing products
 * by their change status.
 */
class LookupResultDto
{
    /** Current page number (for paginated responses) */
    public int $PageNumber = 0;

    /** Number of items per page (for paginated responses) */
    public int $PageSize = 0;

    /** Total number of pages (for paginated responses) */
    public int $TotalPages = 0;

    /** Total number of changed items */
    public int $ItemsChanged = 0;

    /** Total number of deleted items */
    public int $ItemsDeleted = 0;

    /** Total number of items that haven't changed */
    public int $ItemsNotChanged = 0;

    /** Total number of items not found */
    public int $ItemsNotFound = 0;

    /**
     * Array of products that have changed
     *
     * @var RequestStatusItemDto[]|null
     */
    public ?array $Changed = null;

    /**
     * Array of products that have been deleted
     *
     * @var RequestStatusItemDto[]|null
     */
    public ?array $Deleted = null;

    /**
     * Array of identifiers (GTINs/IDs) that were not found
     *
     * @var string[]|null
     */
    public ?array $NotFound = null;

    /**
     * Array of products that haven't changed
     *
     * @var RequestStatusItemDto[]|null
     */
    public ?array $NotChanged = null;

    /**
     * Create a LookupResultDto from an array or stdClass object
     *
     * @param array|object $data The data to map from
     * @return self
     */
    public static function fromData($data): self
    {
        $dto = new self();
        $data = is_array($data) ? (object)$data : $data;

        $dto->PageNumber = $data->PageNumber ?? $data->pageNumber ?? 0;
        $dto->PageSize = $data->PageSize ?? $data->pageSize ?? 0;
        $dto->TotalPages = $data->TotalPages ?? $data->totalPages ?? 0;
        $dto->ItemsChanged = $data->ItemsChanged ?? $data->itemsChanged ?? 0;
        $dto->ItemsDeleted = $data->ItemsDeleted ?? $data->itemsDeleted ?? 0;
        $dto->ItemsNotChanged = $data->ItemsNotChanged ?? $data->itemsNotChanged ?? 0;
        $dto->ItemsNotFound = $data->ItemsNotFound ?? $data->itemsNotFound ?? 0;

        // Map Changed array
        if (isset($data->Changed) || isset($data->changed)) {
            $changed = $data->Changed ?? $data->changed;
            if (is_array($changed)) {
                $dto->Changed = array_map(fn($item) => RequestStatusItemDto::fromData($item), $changed);
            }
        }

        // Map Deleted array
        if (isset($data->Deleted) || isset($data->deleted)) {
            $deleted = $data->Deleted ?? $data->deleted;
            if (is_array($deleted)) {
                $dto->Deleted = array_map(fn($item) => RequestStatusItemDto::fromData($item), $deleted);
            }
        }

        // Map NotFound array (strings)
        if (isset($data->NotFound) || isset($data->notFound) || isset($data->notfound)) {
            $notFound = $data->NotFound ?? $data->notFound ?? $data->notfound;
            if (is_array($notFound)) {
                $dto->NotFound = $notFound;
            }
        }

        // Map NotChanged array
        if (isset($data->NotChanged) || isset($data->notChanged) || isset($data->notchanged)) {
            $notChanged = $data->NotChanged ?? $data->notChanged ?? $data->notchanged;
            if (is_array($notChanged)) {
                $dto->NotChanged = array_map(fn($item) => RequestStatusItemDto::fromData($item), $notChanged);
            }
        }

        return $dto;
    }
}
