# Changelog

All notable changes to `laravel-route-matrix` will be documented in this file.

## v1.2.0 - 2026-01-15

### Release v1.2.0

We are excited to announce **v1.2.0** of the Laravel Route Matrix package! This release focuses on stability, performance, and improving the developer experience with better integrations and testing capabilities.

#### 🚀 New Features

##### ⚡ Response Caching

Significantly reduce your API costs and improve response times by caching Google Routes API responses.

- Supports Laravel's cache drivers (Redis, File, Database, etc.).
- Configurable TTL and driver via `config/google-routes.php`.

##### 🛡️ API Resiliency

Make your application more robust against transient failures.

- **Automatic Retries**: Implements exponential backoff for 5xx server errors.
- **Rate Limiting**: Gracefully handles `429 Too Many Requests` errors.

##### 🔌 Request Middleware

Inject custom Guzzle middleware into the client for advanced use cases like:

- Detailed logging of requests/responses.
- Debugging and tracing.
- Modifying headers dynamically.

##### 📍 Eloquent Integration (`Routable`)

Seamlessly use your Eloquent models in route requests.

- Implement the `Routable` contract and use the `HasRoute` trait.
- Pass models directly: `GoogleRoutes::from($store)->to($user)->get()`.
- Or use the helper: `$store->routeTo($user)->get()`.

##### 🛠️ Quality & Internal Improvements

- **Strict DTOs**: Refactored internal data handling to use strict Data Transfer Objects for `RouteLeg`, `Step`, and `Polyline`, ensuring better type safety.
- **Enhanced Error Handling**: Added specific exceptions for common API issues:
  - `OverQueryLimitException`
  - `RequestDeniedException`
  
- **Testing**: Refactored `GoogleRoutesClient` to be fully testable with standard Guzzle `MockHandler`, removing the need for reflection hacks in tests.
- **Static Analysis**: Codebase now passes Level 5 phpstan checks via `larastan`.

#### 📦 Upgrading

Update your dependency in `composer.json`:

```bash
composer update gowelle/laravel-route-matrix

```
If you are upgrading from v1.0, publish the configuration file to see new caching options:

```bash
php artisan vendor:publish --tag=google-routes-config --force

```
#### 🧪 Testing

We've updated our test suite to align with Guzzle's best practices. You can run the tests using:

```bash
composer test

```
#### full Change Log

**Full Changelog**: https://github.com/gowelle/laravel-route-matrix/compare/v1.1.1...v1.2.0

## [1.2.0] - 2026-01-16

### Added

- **Response Caching**: Built-in caching for Route and Matrix requests to reduce API costs.
- **Resiliency**: Automatic retry mechanism (Exponential Backoff) for 5xx errors and 429 Rate Limiting.
- **Request Middleware**: Support for injecting custom Guzzle middleware for logging/debugging.
- **Helper Traits**: Introduced `Routable` contract and `HasRoute` trait for seamless Eloquent model integration.
- **Strict DTOs**: Refactored internal logic to use strict Data Transfer Objects for better type safety.
- **Enhanced Error Handling**: New exception classes `OverQueryLimitException` and `RequestDeniedException`.
- **Quality**: Integrated `larastan` (Level 5) and `pint` for static analysis and code style.

### Changed

- Refactored `GoogleRoutesClient` to accept a custom `handler` for better testing.
- Updated all tests to use standard Guzzle `MockHandler`.

## v1.1.1 - 2026-01-11

**Full Changelog**: https://github.com/gowelle/laravel-route-matrix/compare/v1.1.0...v1.1.1

## [1.1.0] - 2026-01-11

### Added

- Compute Route Matrix API support for one-to-many and many-to-many distance calculations
- `RouteMatrixRequest` fluent builder for matrix requests
- `RouteMatrixResponse` DTO with helper methods
- `RouteMatrixElement` DTO for individual matrix elements
- `RouteMatrixElementCondition` enum
- Helper methods: `getClosestDestination()`, `getFastestDestination()`, `getClosestOrigin()`, `getFastestOrigin()`
- Sorting methods: `sortedByDistance()`, `sortedByDuration()`
- Matrix conversion: `toMatrix()` for 2D array access
- 13 new integration tests for Route Matrix functionality

## [1.0.0] - 2026-01-11

### Added

- Initial release
- Support for Google Routes API Compute Routes endpoint
- Fluent API for building route requests
- Support for multiple travel modes (DRIVE, WALK, BICYCLE, TWO_WHEELER, TRANSIT)
- Traffic-aware routing with multiple preferences
- Flexible waypoint input (coordinates, Place IDs, addresses)
- Route modifiers (avoid tolls, highways, ferries, indoor)
- Alternative routes support
- Fuel-efficient route calculation
- Waypoint optimization
- Polyline encoding (standard and GeoJSON)
- Comprehensive DTOs for response parsing
- Full test suite with unit, feature, and integration tests
- Support for Laravel 10, 11, and 12
- Support for PHP 8.2, 8.3, and 8.4
