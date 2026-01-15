# Changelog

All notable changes to `laravel-route-matrix` will be documented in this file.

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
