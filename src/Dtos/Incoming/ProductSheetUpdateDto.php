<?php

namespace PSinfoodservice\Dtos\Incoming;

/**
 * @XmlTitle(ElementName="ProductSheetUpdate")
 */
class ProductSheetUpdateDto
{
    /**
     * @XmlElement("logistic")
     * @JsonPropertyName("logistic")
     */
    public ?object $Logistic = null;

    /**
     * @XmlElement("product")
     * @JsonPropertyName("product")
     */
    public ?object $Product = null;

    /**
     * @XmlElement("specification")
     * @JsonPropertyName("specification")
     */
    public ?object $Specification = null;
}