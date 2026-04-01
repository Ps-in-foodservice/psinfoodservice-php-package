<?php

declare(strict_types=1);

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Product impact score item data transfer object
 *
 * Represents a product with its environmental impact score and related information.
 */
class ProductImpactScoreItemDto
{
    /** Logistic ID */
    public int $LogisticId = 0;

    /** Product ID */
    public ?int $ProductId = null;

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

    /** Impact score value */
    public ?int $ImpactScore = null;

    /** CO2 emission value */
    public ?float $Co2Emission = null;

    /** Water usage value */
    public ?float $WaterUsage = null;

    /** Indicates if this product is an outlier */
    public ?bool $IsOutlier = null;

    /** Reason for being an outlier */
    public ?string $OutlierReason = null;

    /** Product category ID */
    public ?int $ProductCategoryId = null;

    /**
     * Product category name translations
     *
     * @var array|null Array of translation objects with LanguageId and Value
     */
    public ?array $ProductCategoryName = null;

    /**
     * Create a ProductImpactScoreItemDto from an array or stdClass object
     *
     * @param array|object $data The data to map from
     * @return self
     */
    public static function fromData($data): self
    {
        $dto = new self();
        $data = is_array($data) ? (object)$data : $data;

        $dto->LogisticId = $data->LogisticId ?? $data->logisticId ?? 0;
        $dto->ProductId = $data->ProductId ?? $data->productId ?? null;
        $dto->Name = isset($data->Name) || isset($data->name) ? ($data->Name ?? $data->name) : null;
        $dto->Number = $data->Number ?? $data->number ?? null;
        $dto->GTIN = $data->GTIN ?? $data->gtin ?? null;
        $dto->TargetMarkets = isset($data->TargetMarkets) || isset($data->targetMarkets) ? ($data->TargetMarkets ?? $data->targetMarkets) : null;
        $dto->LastChanged = isset($data->LastChanged) || isset($data->lastChanged) ? ($data->LastChanged ?? $data->lastChanged) : null;
        $dto->ImpactScore = $data->ImpactScore ?? $data->impactScore ?? null;
        $dto->Co2Emission = $data->Co2Emission ?? $data->co2Emission ?? null;
        $dto->WaterUsage = $data->WaterUsage ?? $data->waterUsage ?? null;
        $dto->IsOutlier = $data->IsOutlier ?? $data->isOutlier ?? null;
        $dto->OutlierReason = $data->OutlierReason ?? $data->outlierReason ?? null;
        $dto->ProductCategoryId = $data->ProductCategoryId ?? $data->productCategoryId ?? null;
        $dto->ProductCategoryName = isset($data->ProductCategoryName) || isset($data->productCategoryName) ? ($data->ProductCategoryName ?? $data->productCategoryName) : null;

        return $dto;
    }
}
