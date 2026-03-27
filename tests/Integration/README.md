# Integration Tests

Integration tests voor de PSPHPPackage SDK.

## Setup

Integration tests vereisen echte API credentials en een actieve verbinding met de PS in Foodservice API.

### Configuratie

Configureer de volgende environment variables:

```bash
PS_API_USERNAME=your_username
PS_API_PASSWORD=your_password
PS_API_ENV=preproduction  # gebruik preproduction voor testen
```

### Het Runnen van Integration Tests

```bash
# Run alleen integration tests
vendor/bin/phpunit --testsuite Integration

# Run met environment variables
PS_API_USERNAME=user PS_API_PASSWORD=pass vendor/bin/phpunit --testsuite Integration
```

## Toekomstige Tests

De volgende integration tests kunnen worden toegevoegd:

### Authentication Flow
- End-to-end login met echte credentials
- Token refresh flow
- Webhook subscription/unsubscription
- Session timeout handling

### Product Operations
- Volledige product sheet CRUD operations
- Multi-language product data
- Product validation en logic tests
- Brand management

### Lookup Operations
- GTIN lookup
- Article number lookup
- Assortment lookup
- Pagination testing

### File Operations
- File upload
- File download
- Security token generation
- Image resizing

### Assortment Management
- Create assortment
- Add/remove items
- Delete assortment
- Pagination

### Master Data
- Fetch all master data types
- Verify master data structure

## Best Practices

1. **Gebruik preproduction environment** - Nooit testen tegen production
2. **Clean up test data** - Verwijder test data na tests
3. **Gebruik unieke identifiers** - Gebruik timestamps of UUIDs voor test data
4. **Rate limiting** - Respecteer API rate limits
5. **Idempotency** - Tests moeten meerdere keren kunnen runnen zonder problemen

## Opmerking

Integration tests zijn momenteel uitgeschakeld in de standaard test suite. Deze moeten expliciet worden aangeroepen met `--testsuite Integration`.
