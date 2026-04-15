# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - 2026-04-15

### Added

- **Producten zoeken op handelseenheid (HE)**: Het is nu mogelijk om producten op te zoeken via het handelseenheidnummer. GTINs worden automatisch genormaliseerd naar 14 cijfers bij de vergelijking, waardoor zowel korte als lange GTIN-notaties correct worden herkend.
- **Product verwijderen via de API**: Via de API kunnen producten nu worden verwijderd. Bij het verwijderen kan een reden worden meegegeven. Synchronisatie van verwijderde producten is ook ondersteund via een apart endpoint.

### Changed

- **Lookup filtert nu alleen op basiseenheden**: Bij alle lookup-methoden (op GTIN, artikelnummer, GLN, PS-ID, merk en assortiment) worden alleen nog basiseenheden teruggegeven. Niet-basiseenheden (zoals displays en omdozen) verschijnen niet meer in de resultaten.
- **BTW-tarief respons uitgebreid**: Het `TaxRate`-object in de productblad-respons bevat nu ook een `id`-veld naast het bestaande `taxrateid`.

### Fixed

- **Dubbele producten in lookup resultaten**: Bij lookups waarbij hetzelfde product meerdere keren in de statuslijst voorkwam (bijv. door meerdere eenheden), wordt nu slechts één resultaat per product teruggegeven.

---

## [1.1.0] - 2026-03-31

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
- **Paginering bij lookup-endpoints**: Alle lookup-methoden (GTIN, artikelnummer, GLN, PS-ID, merk en assortiment) ondersteunen nu paginering. Stuur `PageNumber` (standaard 1) en `PageSize` (standaard 250, max 1000) mee in de request body. De respons bevat de velden `pageNumber`, `pageSize`, `totalPages`, `itemsChanged`, `itemsDeleted`, `itemsNotChanged` en `itemsNotFound` zodat je eenvoudig door meerdere pagina's kunt lopen.
- **Paginering bij assortimentslijst items**: `GET assortments/{id}/items` accepteert nu de queryparameters `pageNumber` en `pageSize` (standaard 250). De respons bevat `pageNumber`, `pageSize`, `totalPages` en `totalItems`. Pagina 1 haalt altijd verse data op; de volgende pagina's worden vanuit een tijdelijke cache geserveerd (2 minuten). Bij een verlopen cache ontvang je een `410 Gone`-respons en dien je opnieuw vanaf pagina 1 te beginnen.
- **Paginator**: New helper class for iterating through paginated lookup results
- **Alternatieve GTINs bij logistiek product**: Het logistiek product (`GET`/`PUT` logistic) bevat nu een `alternativegtins`-lijst met extra GTINs (bijv. consumenteneenheden of EAN-varianten). Bij een `PUT` kunnen alternatieve GTINs worden meegegeven via `alternativegtins > alternativegtin` met de velden `gtin` en `alternativegtintypeid`.
- **MijnPS: ImpactScore endpoint**: Nieuw endpoint `GET mijnps/impactscore/{relationId}` dat de eigen producten retourneert verrijkt met milieu-impact gegevens: impactscore, CO₂-uitstoot (`rawgreenhouseimpact`), waterverbruik, outlier-status en productcategorie.
- **MijnPS: Relaties ophalen**: Nieuw endpoint `GET mijnps/relations` voor het ophalen van alle beschikbare relaties.
- **Relatie-certificaten beheren**: Certificaten voor een relatie kunnen nu worden aangemaakt/bijgewerkt (`PUT`) en verwijderd (`DELETE`) via `mijnps/relationCertificates/{relationId}`.
- **Push notificatie bij assortiment match**: Wanneer een product wordt gematcht aan een assortimentslijst, ontvangt de klant direct een webhook-notificatie (ProductUpdateNotification) zodat de productinformatie meteen opgehaald kan worden.
- **Lookup minimale variant** (PHP): Alle lookup-methoden accepteren nu een `$minimal`-parameter. Bij `true` wordt het `/minimal`-endpoint gebruikt dat alleen identifiers teruggeeft (sneller, minder data).
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

- **Assortimentslijsten: geen limiet meer** ⚠️: Het veld `assortmentTypeId` is verwijderd uit de API. Stuur dit veld niet meer mee bij het aanmaken van een assortimentslijst. Assortimentslijsten kunnen nu onbeperkt worden aangemaakt.
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

- **GTIN leeg bewaren**: Logistieke eenheden zonder barcode (identificatie via artikelnummer) behouden nu correct een lege/null GTIN-waarde. Voorheen werd een lege GTIN ten onrechte gevuld met nullen.
- **Foutmeldingen verbeterd**: De API geeft nu consistent `400 Bad Request` terug bij ontbrekende verplichte velden (bijv. leeg request body bij lookup, ontbrekende subscribe-URL bij webhook) in plaats van een onverwachte serverfout.
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
