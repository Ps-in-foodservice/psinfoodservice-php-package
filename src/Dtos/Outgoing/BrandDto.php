<?php

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Brand data transfer object
 *
 * Represents a brand with its properties and ownership information.
 */
class BrandDto
{
    /** Brand ID */
    public int $Id = 0;

    /** Brand owner relation ID */
    public int $Brandownerid = 0;

    /** Brand owner name */
    public ?string $Brandownername = null;

    /** Brand owner GLN (Global Location Number) */
    public ?string $Brandownergln = null;

    /** Brand name */
    public ?string $Name = null;

    /** Brand image URL */
    public ?string $Image = null;

    /** Indicates if this is a private label */
    public ?bool $IsPrivateLabel = null;

    /** Indicates if brand is publicly visible */
    public ?bool $IsPubliclyVisible = null;

    /** Third party identifier */
    public ?string $ThirdPartyId = null;

    /** Indicates if brand is visible in producer detail view */
    public ?bool $IsVisibleInProducerDetail = null;

    /** Declaration format type ID */
    public int $DeclarationFormatTypeId = 0;

    /** Indicates if producers can publish specifications for this brand */
    public ?bool $AllowProducersToPublishSpecification = null;

    /**
     * Create a BrandDto from an array or stdClass object
     *
     * @param array|object $data The data to map from
     * @return self
     */
    public static function fromData($data): self
    {
        $dto = new self();
        $data = is_array($data) ? (object)$data : $data;

        $dto->Id = $data->Id ?? $data->id ?? 0;
        $dto->Brandownerid = $data->Brandownerid ?? $data->brandownerid ?? 0;
        $dto->Brandownername = $data->Brandownername ?? $data->brandownername ?? null;
        $dto->Brandownergln = $data->Brandownergln ?? $data->brandownergln ?? null;
        $dto->Name = $data->Name ?? $data->name ?? null;
        $dto->Image = $data->Image ?? $data->image ?? null;
        $dto->IsPrivateLabel = $data->IsPrivateLabel ?? $data->isprivatelabel ?? null;
        $dto->IsPubliclyVisible = $data->IsPubliclyVisible ?? $data->ispubliclyvisible ?? null;
        $dto->ThirdPartyId = $data->ThirdPartyId ?? $data->thirdpartyid ?? null;
        $dto->IsVisibleInProducerDetail = $data->IsVisibleInProducerDetail ?? $data->isvisibleinproducerdetail ?? null;
        $dto->DeclarationFormatTypeId = $data->DeclarationFormatTypeId ?? $data->declarationformattypeid ?? 0;
        $dto->AllowProducersToPublishSpecification = $data->AllowProducersToPublishSpecification ?? $data->allowproducerstopublishspecification ?? null;

        return $dto;
    }
}
