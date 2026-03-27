<?php

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Assortment item data transfer object
 *
 * Represents a single item within an assortment list.
 */
class AssortmentItemDto
{
    /** Item ID */
    public int $Id = 0;

    /** Article number */
    public ?string $ArticleNumber = null;

    /** Article name */
    public ?string $ArticleName = null;

    /** Brand name of the article */
    public ?string $ArticleBrand = null;

    /** Consumer unit GTIN */
    public ?string $GTINCE = null;

    /** Handling/Dispatch unit GTIN */
    public ?string $GTINHE = null;

    /** Relation GLN (Global Location Number) */
    public ?string $RelationGln = null;

    /** Relation name */
    public ?string $RelationName = null;

    /** Relation-specific article number */
    public ?string $RelationArticleNumber = null;

    /**
     * Create an AssortmentItemDto from an array or stdClass object
     *
     * @param array|object $data The data to map from
     * @return self
     */
    public static function fromData($data): self
    {
        $dto = new self();
        $data = is_array($data) ? (object)$data : $data;

        $dto->Id = $data->Id ?? $data->id ?? 0;
        $dto->ArticleNumber = $data->ArticleNumber ?? $data->articleNumber ?? null;
        $dto->ArticleName = $data->ArticleName ?? $data->articleName ?? null;
        $dto->ArticleBrand = $data->ArticleBrand ?? $data->articleBrand ?? null;
        $dto->GTINCE = $data->GTINCE ?? $data->gtince ?? null;
        $dto->GTINHE = $data->GTINHE ?? $data->gtinhe ?? null;
        $dto->RelationGln = $data->RelationGln ?? $data->relationGln ?? null;
        $dto->RelationName = $data->RelationName ?? $data->relationName ?? null;
        $dto->RelationArticleNumber = $data->RelationArticleNumber ?? $data->relationArticleNumber ?? null;

        return $dto;
    }
}
