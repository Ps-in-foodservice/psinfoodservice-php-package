<?php

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * File content metadata
 */
class FileContentDto
{
    /**
     * File ID
     *
     * @var int
     */
    public int $FileId = 0;

    /**
     * File type information
     *
     * @var FileTypeDto|null
     */
    public ?FileTypeDto $FileType = null;

    /**
     * File usage type information
     *
     * @var FileUsageTypeDto|null
     */
    public ?FileUsageTypeDto $FileUsageType = null;

    /**
     * File name (without extension)
     *
     * @var string|null
     */
    public ?string $FileName = null;

    /**
     * File extension (e.g., "jpg", "pdf")
     *
     * @var string|null
     */
    public ?string $FileExtension = null;

    /**
     * Full file name (with extension)
     *
     * @var string|null
     */
    public ?string $FileFullName = null;

    /**
     * User-friendly display name
     *
     * @var string|null
     */
    public ?string $FriendlyName = null;

    /**
     * File description
     *
     * @var string|null
     */
    public ?string $Description = null;

    /**
     * Security token for accessing the file
     *
     * @var string|null
     */
    public ?string $SecurityToken = null;

    /**
     * Image pixel width (0 if not an image)
     *
     * @var int
     */
    public int $PixelWidth = 0;

    /**
     * Image pixel height (0 if not an image)
     *
     * @var int
     */
    public int $PixelHeight = 0;

    /**
     * Full URL to access the file
     *
     * @var string|null
     */
    public ?string $Url = null;

    /**
     * Whether this is a high quality image
     *
     * @var bool
     */
    public bool $IsHighQuality = false;

    /**
     * Whether the image has transparency (alpha channel)
     *
     * @var bool
     */
    public bool $HasTransparency = false;

    /**
     * Create a FileContentDto from API response data
     *
     * @param mixed $data API response data (array or object)
     * @return self
     */
    public static function fromData($data): self
    {
        $dto = new self();
        $data = is_array($data) ? (object)$data : $data;

        // Map simple properties with case-insensitive access
        $dto->FileId = $data->fileid ?? $data->fileId ?? $data->FileId ?? 0;
        $dto->FileName = $data->filename ?? $data->fileName ?? $data->FileName ?? null;
        $dto->FileExtension = $data->fileextension ?? $data->fileExtension ?? $data->FileExtension ?? null;
        $dto->FileFullName = $data->filefullname ?? $data->fileFullName ?? $data->FileFullName ?? null;
        $dto->FriendlyName = $data->friendlyname ?? $data->friendlyName ?? $data->FriendlyName ?? null;
        $dto->Description = $data->description ?? $data->Description ?? null;
        $dto->SecurityToken = $data->securitytoken ?? $data->securityToken ?? $data->SecurityToken ?? null;
        $dto->PixelWidth = $data->pixelwidth ?? $data->pixelWidth ?? $data->PixelWidth ?? 0;
        $dto->PixelHeight = $data->pixelheight ?? $data->pixelHeight ?? $data->PixelHeight ?? 0;
        $dto->Url = $data->url ?? $data->Url ?? null;
        $dto->IsHighQuality = $data->ishighquality ?? $data->isHighQuality ?? $data->IsHighQuality ?? false;
        $dto->HasTransparency = $data->hastransparency ?? $data->hasTransparency ?? $data->HasTransparency ?? false;

        // Map nested objects
        if (isset($data->filetype) || isset($data->fileType) || isset($data->FileType)) {
            $fileTypeData = $data->filetype ?? $data->fileType ?? $data->FileType;
            $dto->FileType = FileTypeDto::fromData($fileTypeData);
        }

        if (isset($data->fileusagetype) || isset($data->fileUsageType) || isset($data->FileUsageType)) {
            $fileUsageTypeData = $data->fileusagetype ?? $data->fileUsageType ?? $data->FileUsageType;
            $dto->FileUsageType = FileUsageTypeDto::fromData($fileUsageTypeData);
        }

        return $dto;
    }
}
