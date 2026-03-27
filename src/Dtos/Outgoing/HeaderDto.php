<?php

declare(strict_types=1);

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Header information for API responses
 *
 * @XmlTitle(ElementName="Header")
 */
class HeaderDto
{
    /**
     * API provider name
     *
     * @XmlElement("provider")
     * @JsonPropertyName("provider")
     */
    public string $Provider = 'PS In Foodservice';

    /**
     * API version
     *
     * @XmlElement("version")
     * @JsonPropertyName("version")
     */
    public string $Version = '7.0.0.0';

    /**
     * HTTP action type (GET, PUT, POST, DELETE)
     *
     * @XmlElement("actiontype")
     * @JsonPropertyName("actiontype")
     */
    public string $ActionType = 'GET';

    /**
     * Trace ID for request tracking
     *
     * @XmlElement("traceid")
     * @JsonPropertyName("traceid")
     */
    public ?string $TraceId = null;

    /**
     * Execution time in milliseconds
     *
     * @XmlElement("executiontime")
     * @JsonPropertyName("executiontime")
     */
    public string $ExecutionTime = '0';
}
