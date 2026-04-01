# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0]

### Added

- **MijnPSService**: New service for MijnPS operations
    - `uploadAssortment()` - Upload assortment file from path
    - `uploadAssortmentContent()` - Upload assortment from string content
- **BrandService**: Added `getAllByDate()` method to retrieve brands added after a specific date
- **AssetService**: New service for asset retrieval
    - `getAsset()` - Get asset metadata by ID
    - `getAssetsFromLogistic()` - Get all assets for a logistic
    - `getAssetsFromLogisticByLanguage()` - Get assets for a logistic in a specific language
- **RelationService**: New service for relation data
    - `getProducers()` - Get list of producers
    - `getBrandOwners()` - Get list of brand owners
- **AssetDto**: New DTO for asset information
- **Paginator**: New helper class for iterating through paginated lookup results
- **Service Interfaces**: Added interfaces for improved testability
    - `AuthenticationServiceInterface`
    - `BrandServiceInterface`
    - `LookupServiceInterface`
- **PSApiException**: Added `getTraceId()` method to retrieve trace ID separately from message
- **PSinfoodserviceClient**: Added `clearTokens()` method to clear stored authentication tokens
- **HelperService**: Added data-only methods for programmatic data access
    - `getNutrientsData()` - Get nutrient data as array (no HTML)
    - `getAllergensData()` - Get allergen data as array (no HTML)
- **AsyncClient**: New helper for concurrent/parallel API requests
    - Execute multiple independent requests in parallel
    - Configurable concurrency limit
    - Callback-based result handling
- **CachedMasterService**: New service wrapper with built-in caching
    - Cache master data to reduce redundant API calls
    - Configurable TTL and custom cache backends
    - PSR-16 compatible cache interface
- **InMemoryCache**: Simple in-memory cache implementation
- **LoggingMiddleware**: New middleware for request/response logging
    - Configurable log levels
    - Automatic sensitive header redaction
    - Request timing information
    - Optional body logging
- **CacheInterface**: New interface for cache implementations
- **PSinfoodserviceClient**: Added convenience methods
    - `async()` - Create AsyncClient instance
    - `cachedMasters()` - Create CachedMasterService instance

### Changed

- **Method naming**: Standardized all public methods to camelCase (PSR-1)
    - `BrandService::All()` -> `getAll()`
    - `BrandService::MyBrands()` -> `getMyBrands()`
    - `ImpactScoreService::AllScores()` -> `getAllScores()`
    - `ImpactScoreService::GetScore()` -> `getScore()`
    - `MasterService::GetAllMasters()` -> `getAllMasters()`
    - And other Master service methods
- **AuthenticationService::logoff()**: Now clears local tokens after successful server logout
- **PSApiException**: TraceId is now stored separately and appended to message in formatted style

### Fixed

- Token cleanup after logout - local tokens are now properly cleared

## [1.0.0] - Initial Release

### Added

- Authentication service with login, logout, token refresh, webhook subscribe/unsubscribe
- WebApi service for product sheets and my products
- Lookup service with all lookup methods (GTIN, PSID, ArticleNumber, GLN, Assortment, BrandId, All)
- Assortment service for managing assortment lists
- Brand service for brand information
- File service for file and image retrieval
- ImpactScore service for environmental impact data
- Master service for reference data
- Validation service for product validation
- Helper service for data formatting and display
- Automatic token refresh functionality
- Retry middleware with exponential backoff
- Rate limit middleware with auto-wait option
- PSR-3 logging support
- Comprehensive PHPDoc documentation
- PHPUnit test suite
