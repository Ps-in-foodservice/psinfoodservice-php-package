<?php

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * File type information
 */
class FileTypeDto
{
    /**
     * File type ID
     *
     * @var int
     */
    public int $Id = 0;

    /**
     * File type name
     *
     * @var string|null
     */
    public ?string $Name = null;

    /**
     * Whether this is a file extension
     *
     * @var bool
     */
    public bool $IsFileExtension = false;

    /**
     * Create a FileTypeDto from API response data
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
        $dto->IsFileExtension = $data->isfileextension ?? $data->isFileExtension ?? $data->IsFileExtension ?? false;

        return $dto;
    }
}
