<?php

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * File usage type information
 */
class FileUsageTypeDto
{
    /**
     * File usage type ID
     *
     * @var int
     */
    public int $Id = 0;

    /**
     * Translations of the usage type name
     *
     * @var array|null Array of translation objects
     */
    public ?array $Name = null;

    /**
     * Create a FileUsageTypeDto from API response data
     *
     * @param mixed $data API response data (array or object)
     * @return self
     */
    public static function fromData($data): self
    {
        $dto = new self();
        $data = is_array($data) ? (object)$data : $data;

        // Map properties with case-insensitive access
        $dto->Id = $data->id ?? $data->Id ?? 0;
        $dto->Name = $data->name ?? $data->Name ?? null;

        return $dto;
    }
}
