<?php

declare(strict_types=1);

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Product impact score list data transfer object
 *
 * Represents a list of products with their environmental impact scores.
 */
class ProductImpactScoreListDto
{
    /** Total number of results */
    public int $Results = 0;

    /**
     * Array of product impact score items
     *
     * @var ProductImpactScoreItemDto[]|null
     */
    public ?array $Items = null;

    /**
     * Create a ProductImpactScoreListDto from an array or stdClass object
     *
     * @param array|object $data The data to map from
     * @return self
     */
    public static function fromData($data): self
    {
        $dto = new self();
        $data = is_array($data) ? (object)$data : $data;

        $dto->Results = $data->Results ?? $data->results ?? 0;

        // Map items array
        if (isset($data->Items) || isset($data->items)) {
            $items = $data->Items ?? $data->items;
            if (is_array($items)) {
                $dto->Items = array_map(fn($item) => ProductImpactScoreItemDto::fromData($item), $items);
            }
        }

        return $dto;
    }
}
