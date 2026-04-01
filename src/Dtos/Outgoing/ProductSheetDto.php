<?php

declare(strict_types=1);

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Product sheet data transfer object
 *
 * Represents the complete product sheet response from the API.
 * Contains header information, summary, logistic details, product info, and specification.
 */
class ProductSheetDto
{
    /**
     * Response header information
     *
     * @var object|null Contains provider, version, actionType, traceId, executionTime
     */
    public ?object $header = null;

    /**
     * Product summary information
     *
     * @var object|null Contains logisticId, productId, gtin, name, brandName, etc.
     */
    public ?object $summary = null;

    /**
     * Logistic information
     *
     * @var object|null Contains packaging, dimensions, weight, storage conditions, etc.
     */
    public ?object $logistic = null;

    /**
     * Product information
     *
     * @var object|null Contains product details, characteristics, labels, etc.
     */
    public ?object $product = null;

    /**
     * Specification information
     *
     * @var object|null Contains ingredients, allergens, nutrients, etc.
     */
    public ?object $specification = null;

    /**
     * Create a ProductSheetDto from an array or stdClass object
     *
     * @param array|object $data The data to map from
     * @return self
     */
    public static function fromData($data): self
    {
        $dto = new self();
        $data = is_array($data) ? (object)$data : $data;

        $dto->header = $data->header ?? $data->Header ?? null;
        $dto->summary = $data->summary ?? $data->Summary ?? null;
        $dto->logistic = $data->logistic ?? $data->Logistic ?? null;
        $dto->product = $data->product ?? $data->Product ?? null;
        $dto->specification = $data->specification ?? $data->Specification ?? null;

        return $dto;
    }

    /**
     * Get the logistic ID from the summary
     *
     * @return int|null
     */
    public function getLogisticId(): ?int
    {
        if ($this->summary === null) {
            return null;
        }

        return $this->summary->logisticId ?? $this->summary->LogisticId ?? null;
    }

    /**
     * Get the product ID from the summary
     *
     * @return int|null
     */
    public function getProductId(): ?int
    {
        if ($this->summary === null) {
            return null;
        }

        return $this->summary->productId ?? $this->summary->ProductId ?? null;
    }

    /**
     * Get the GTIN from the summary
     *
     * @return string|null
     */
    public function getGtin(): ?string
    {
        if ($this->summary === null) {
            return null;
        }

        return $this->summary->gtin ?? $this->summary->GTIN ?? null;
    }

    /**
     * Get the product name translations from the summary
     *
     * @return array|null Array of translation objects with LanguageId and Value
     */
    public function getName(): ?array
    {
        if ($this->summary === null) {
            return null;
        }

        $name = $this->summary->name ?? $this->summary->Name ?? null;

        return is_array($name) ? $name : null;
    }

    /**
     * Get the brand name from the summary
     *
     * @return string|null
     */
    public function getBrandName(): ?string
    {
        if ($this->summary === null) {
            return null;
        }

        return $this->summary->brandName ?? $this->summary->BrandName ?? null;
    }
}
