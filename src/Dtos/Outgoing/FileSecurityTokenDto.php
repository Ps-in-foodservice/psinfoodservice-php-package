<?php

declare(strict_types=1);

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * File security token response
 */
class FileSecurityTokenDto
{
    /**
     * File ID
     *
     * @var int
     */
    public int $FileId = 0;

    /**
     * Security token (GUID) for accessing the file
     *
     * @var string|null
     */
    public ?string $SecurityToken = null;

    /**
     * Full URL to access the file with the security token
     *
     * @var string|null
     */
    public ?string $Url = null;

    /**
     * Create a FileSecurityTokenDto from API response data
     *
     * @param mixed $data API response data (array or object)
     * @return self
     */
    public static function fromData($data): self
    {
        $dto = new self();
        $data = is_array($data) ? (object)$data : $data;

        // Map properties with case-insensitive access
        $dto->FileId = $data->fileid ?? $data->fileId ?? $data->FileId ?? 0;
        $dto->SecurityToken = $data->securitytoken ?? $data->securityToken ?? $data->SecurityToken ?? null;
        $dto->Url = $data->url ?? $data->Url ?? null;

        return $dto;
    }
}
