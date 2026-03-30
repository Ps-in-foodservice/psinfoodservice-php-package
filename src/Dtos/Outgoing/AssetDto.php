<?php

declare(strict_types=1);

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Data transfer object for asset information
 *
 * Represents a digital asset (image, document) associated with a product.
 */
class AssetDto
{
    /** The unique identifier of the asset */
    public int $Id = 0;

    /** The file ID associated with this asset */
    public int $FileId = 0;

    /** The logistic ID this asset is associated with */
    public int $LogisticId = 0;

    /** The asset type ID */
    public ?int $AssetTypeId = null;

    /** The asset type name/description */
    public ?string $AssetType = null;

    /** The facing type ID (for product images) */
    public ?int $FacingTypeId = null;

    /** The facing type name/description */
    public ?string $FacingType = null;

    /** The angle type ID (for product images) */
    public ?int $AngleTypeId = null;

    /** The angle type name/description */
    public ?string $AngleType = null;

    /** The format type ID */
    public ?int $FormatTypeId = null;

    /** The format type name/description */
    public ?string $FormatType = null;

    /** The asset label/name */
    public ?string $Label = null;

    /** The asset source ID */
    public ?int $SourceId = null;

    /** The asset source name/description */
    public ?string $Source = null;

    /** Whether this is the primary/default asset */
    public bool $IsDefault = false;

    /** The pixel width of the asset (for images) */
    public ?int $PixelWidth = null;

    /** The pixel height of the asset (for images) */
    public ?int $PixelHeight = null;

    /** The file size in bytes */
    public ?int $FileSize = null;

    /** The file extension */
    public ?string $FileExtension = null;

    /** The full file name including extension */
    public ?string $FileName = null;

    /** The URL to access the asset */
    public ?string $Url = null;

    /** The security token for accessing the asset */
    public ?string $SecurityToken = null;

    /** The date the asset was created */
    public ?string $CreatedDate = null;

    /** The date the asset was last modified */
    public ?string $ModifiedDate = null;

    /**
     * Create an AssetDto from an array or stdClass object
     *
     * @param array|object $data The data to map from
     * @return self
     */
    public static function fromData($data): self
    {
        $dto = new self();
        $data = is_array($data) ? (object)$data : $data;

        $dto->Id = $data->Id ?? $data->id ?? 0;
        $dto->FileId = $data->FileId ?? $data->fileId ?? 0;
        $dto->LogisticId = $data->LogisticId ?? $data->logisticId ?? 0;

        $dto->AssetTypeId = $data->AssetTypeId ?? $data->assetTypeId ?? null;
        $dto->AssetType = $data->AssetType ?? $data->assetType ?? null;

        $dto->FacingTypeId = $data->FacingTypeId ?? $data->facingTypeId ?? null;
        $dto->FacingType = $data->FacingType ?? $data->facingType ?? null;

        $dto->AngleTypeId = $data->AngleTypeId ?? $data->angleTypeId ?? null;
        $dto->AngleType = $data->AngleType ?? $data->angleType ?? null;

        $dto->FormatTypeId = $data->FormatTypeId ?? $data->formatTypeId ?? null;
        $dto->FormatType = $data->FormatType ?? $data->formatType ?? null;

        $dto->Label = $data->Label ?? $data->label ?? null;

        $dto->SourceId = $data->SourceId ?? $data->sourceId ?? null;
        $dto->Source = $data->Source ?? $data->source ?? null;

        $dto->IsDefault = $data->IsDefault ?? $data->isDefault ?? false;

        $dto->PixelWidth = $data->PixelWidth ?? $data->pixelWidth ?? null;
        $dto->PixelHeight = $data->PixelHeight ?? $data->pixelHeight ?? null;
        $dto->FileSize = $data->FileSize ?? $data->fileSize ?? null;
        $dto->FileExtension = $data->FileExtension ?? $data->fileExtension ?? null;
        $dto->FileName = $data->FileName ?? $data->fileName ?? null;

        $dto->Url = $data->Url ?? $data->url ?? null;
        $dto->SecurityToken = $data->SecurityToken ?? $data->securityToken ?? null;

        $dto->CreatedDate = $data->CreatedDate ?? $data->createdDate ?? null;
        $dto->ModifiedDate = $data->ModifiedDate ?? $data->modifiedDate ?? null;

        return $dto;
    }
}
