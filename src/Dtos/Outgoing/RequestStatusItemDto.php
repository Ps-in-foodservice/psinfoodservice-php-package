<?php

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Request status item data transfer object
 *
 * Represents a product item in a lookup result.
 */
class RequestStatusItemDto
{
    /** Logistic ID */
    public int $LogisticId = 0;

    /** Product ID */
    public int $ProductId = 0;

    /**
     * Product name translations
     *
     * @var array|null Array of translation objects with LanguageId and Value
     */
    public ?array $Name = null;

    /** Article number */
    public ?string $Number = null;

    /** GTIN (Global Trade Item Number) */
    public ?string $GTIN = null;

    /**
     * Target markets
     *
     * @var array|null Array of target market objects
     */
    public ?array $TargetMarkets = null;

    /** Last changed timestamp */
    public ?string $LastChanged = null;

    /**
     * Reason for change/status
     *
     * @var array|null Array of translation objects with LanguageId and Value
     */
    public ?array $Reason = null;

    /**
     * Create a RequestStatusItemDto from an array or stdClass object
     *
     * @param array|object $data The data to map from
     * @return self
     */
    public static function fromData($data): self
    {
        $dto = new self();
        $data = is_array($data) ? (object)$data : $data;

        $dto->LogisticId = $data->LogisticId ?? $data->logisticId ?? $data->logisticid ?? 0;
        $dto->ProductId = $data->ProductId ?? $data->productId ?? 0;
        $dto->Name = isset($data->Name) || isset($data->name) ? ($data->Name ?? $data->name) : null;
        $dto->Number = $data->Number ?? $data->number ?? $data->ArticleNumber ?? $data->articlenumber ?? null;
        $dto->GTIN = $data->GTIN ?? $data->gtin ?? null;
        $dto->TargetMarkets = isset($data->TargetMarkets) || isset($data->targetMarkets) ? ($data->TargetMarkets ?? $data->targetMarkets) : null;
        $dto->LastChanged = isset($data->LastChanged) || isset($data->lastChanged) || isset($data->lastchanged) ? ($data->LastChanged ?? $data->lastChanged ?? $data->lastchanged) : null;
        $dto->Reason = isset($data->Reason) || isset($data->reason) ? ($data->Reason ?? $data->reason) : null;

        return $dto;
    }
}
