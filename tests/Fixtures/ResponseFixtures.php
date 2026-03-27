<?php

declare(strict_types=1);

namespace PSinfoodservice\Tests\Fixtures;

/**
 * Fixture data for API responses
 * Provides reusable mock data for testing
 */
class ResponseFixtures
{
    /**
     * Mock login response
     *
     * @return array
     */
    public static function loginResponse(): array
    {
        return [
            'accesstoken' => 'mock-access-token-12345',
            'refreshtoken' => 'mock-refresh-token-67890',
            'expiresin' => 3600
        ];
    }

    /**
     * Mock refresh token response
     *
     * @return array
     */
    public static function refreshTokenResponse(): array
    {
        return [
            'accessToken' => 'mock-new-access-token',
            'refreshToken' => 'mock-new-refresh-token',
            'expiresIn' => 3600
        ];
    }

    /**
     * Mock brand response
     *
     * @return array
     */
    public static function brandResponse(): array
    {
        return [
            'Id' => 123,
            'Name' => 'Test Brand',
            'Brandownerid' => 456,
            'Brandownername' => 'Test Owner',
            'Brandownergln' => '1234567890123',
            'IsPrivateLabel' => false,
            'IsPubliclyVisible' => true,
            'Image' => 'https://example.com/brand-logo.jpg',
            'ThirdPartyId' => 'brand-ext-123',
            'IsVisibleInProducerDetail' => true,
            'DeclarationFormatTypeId' => 1,
            'AllowProducersToPublishSpecification' => false
        ];
    }

    /**
     * Mock lookup response
     *
     * @return array
     */
    public static function lookupResponse(): array
    {
        return [
            'PageNumber' => 1,
            'PageSize' => 50,
            'TotalPages' => 1,
            'ItemsChanged' => 2,
            'ItemsDeleted' => 0,
            'ItemsNotChanged' => 0,
            'ItemsNotFound' => 0,
            'Changed' => [
                [
                    'LogisticId' => 1,
                    'ProductId' => 100,
                    'Name' => [
                        ['LanguageId' => 1, 'Value' => 'Product 1']
                    ],
                    'Number' => 'ART-001',
                    'GTIN' => '8712345678901',
                    'TargetMarkets' => [1, 2],
                    'LastChanged' => '2024-01-15T10:30:00Z',
                    'Reason' => [
                        ['LanguageId' => 1, 'Value' => 'Updated']
                    ]
                ],
                [
                    'LogisticId' => 2,
                    'ProductId' => 200,
                    'Name' => [
                        ['LanguageId' => 1, 'Value' => 'Product 2']
                    ],
                    'Number' => 'ART-002',
                    'GTIN' => '8712345678902',
                    'TargetMarkets' => [1],
                    'LastChanged' => '2024-01-16T14:20:00Z',
                    'Reason' => [
                        ['LanguageId' => 1, 'Value' => 'New product']
                    ]
                ]
            ],
            'Deleted' => [],
            'NotFound' => [],
            'NotChanged' => []
        ];
    }

    /**
     * Mock impact score response
     *
     * @return array
     */
    public static function impactScoreResponse(): array
    {
        return [
            'LogisticId' => 123,
            'ProductId' => 456,
            'Name' => [
                ['LanguageId' => 1, 'Value' => 'Test Product']
            ],
            'Number' => 'ART-123',
            'GTIN' => '8712345678901',
            'TargetMarkets' => [1],
            'LastChanged' => '2024-01-15T10:00:00Z',
            'ImpactScore' => 4,
            'Co2Emission' => 1.25,
            'WaterUsage' => 50.75,
            'IsOutlier' => false,
            'OutlierReason' => null,
            'ProductCategoryId' => 10,
            'ProductCategoryName' => [
                ['LanguageId' => 1, 'Value' => 'Dairy Products']
            ]
        ];
    }

    /**
     * Mock validation error response
     *
     * @return array
     */
    public static function validationErrorResponse(): array
    {
        return [
            'detail' => 'Validation failed',
            'errors' => [
                [
                    'field' => 'Product.Name',
                    'message' => 'Product name is required'
                ],
                [
                    'field' => 'Logistic.GTIN',
                    'message' => 'GTIN must be 13 digits'
                ]
            ],
            'traceId' => 'validation-trace-123'
        ];
    }

    /**
     * Mock rate limit response
     *
     * @return array
     */
    public static function rateLimitResponse(): array
    {
        return [
            'detail' => 'Rate limit exceeded',
            'traceId' => 'rate-limit-trace-456',
            'rateLimit' => 10
        ];
    }

    /**
     * Mock product sheet response
     *
     * @return array
     */
    public static function productSheetResponse(): array
    {
        return [
            'Logistic' => [
                'Id' => 123,
                'GTIN' => '8712345678901',
                'ArticleNumber' => 'ART-123',
                'ArticleName' => 'Test Product'
            ],
            'Product' => [
                'Id' => 456,
                'BrandId' => 10,
                'BrandName' => 'Test Brand',
                'Name' => [
                    ['LanguageId' => 1, 'Value' => 'Test Product NL'],
                    ['LanguageId' => 2, 'Value' => 'Test Product FR']
                ]
            ],
            'Specification' => [
                'Id' => 789,
                'Weight' => 1.5,
                'WeightUnitId' => 1
            ]
        ];
    }

    /**
     * Mock file content response
     *
     * @return array
     */
    public static function fileContentResponse(): array
    {
        return [
            'FileId' => 999,
            'FileType' => [
                'Id' => 1,
                'Name' => 'JPG',
                'IsFileExtension' => true
            ],
            'FileUsageType' => [
                'Id' => 5,
                'Name' => [
                    ['LanguageId' => 1, 'Value' => 'Product Image']
                ]
            ],
            'FileName' => 'product-image',
            'FileExtension' => 'jpg',
            'FileFullName' => 'product-image.jpg',
            'FriendlyName' => 'Main Product Image',
            'Description' => 'High resolution product photo',
            'SecurityToken' => 'sec-token-abc123',
            'PixelWidth' => 1920,
            'PixelHeight' => 1080,
            'Url' => 'https://files.example.com/product-image.jpg',
            'IsHighQuality' => true,
            'HasTransparency' => false
        ];
    }

    /**
     * Mock assortment response
     *
     * @return array
     */
    public static function assortmentResponse(): array
    {
        return [
            'Id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'Name' => 'Test Assortment',
            'PageNumber' => 1,
            'PageSize' => 50,
            'TotalPages' => 1,
            'TotalItems' => 2,
            'Items' => [
                [
                    'Id' => 1,
                    'ArticleNumber' => 'ART-001',
                    'ArticleName' => 'Product 1',
                    'ArticleBrand' => 'Brand A',
                    'GTINCE' => '8712345678901',
                    'GTINHE' => '8712345678918',
                    'RelationGln' => '1234567890123',
                    'RelationName' => 'Supplier A',
                    'RelationArticleNumber' => 'SUP-ART-001'
                ],
                [
                    'Id' => 2,
                    'ArticleNumber' => 'ART-002',
                    'ArticleName' => 'Product 2',
                    'ArticleBrand' => 'Brand B',
                    'GTINCE' => '8712345678902',
                    'GTINHE' => '8712345678919',
                    'RelationGln' => '1234567890124',
                    'RelationName' => 'Supplier B',
                    'RelationArticleNumber' => 'SUP-ART-002'
                ]
            ]
        ];
    }

    /**
     * Mock validation result response
     *
     * @return array
     */
    public static function validationResultResponse(): array
    {
        return [
            'Header' => [
                'Provider' => 'PS In Foodservice',
                'Version' => '7.0.0.0',
                'ActionType' => 'VALIDATE',
                'TraceId' => 'trace-123',
                'ExecutionTime' => '125'
            ],
            'IsValid' => false,
            'Errors' => [
                [
                    'Position' => 'Product.Name',
                    'ErrorMessage' => 'Name is required'
                ]
            ],
            'TraceId' => 'trace-123',
            'ErrorMessage' => null
        ];
    }

    /**
     * Mock logic result response
     *
     * @return array
     */
    public static function logicResultResponse(): array
    {
        return [
            'Header' => [
                'Provider' => 'PS In Foodservice',
                'Version' => '7.0.0.0',
                'ActionType' => 'LOGIC',
                'TraceId' => 'trace-456',
                'ExecutionTime' => '250'
            ],
            'IsValid' => true,
            'Errors' => [],
            'LogicRules' => [
                'Product name must not contain special characters',
                'GTIN must be valid'
            ],
            'TraceId' => 'trace-456',
            'ErrorMessage' => null
        ];
    }
}
