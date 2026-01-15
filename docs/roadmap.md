# Roadmap

This document outlines the future development plans for the `laravel-route-matrix` package. Recommendations and contributions are welcome!

## v1.x (Current Major Version)

Focus: Stability, Performance, and Developer Experience.

### Stability & Quality
- [x] **Static Analysis**: Integrate `larastan/larastan` (Level 5+) into the CI pipeline to catch type errors early.
- [x] **Code Style**: Enforce consistent styling using `laravel/pint` in GitHub Actions.
- [x] **Resiliency**: Implement automatic retry logic for transient API failures (5xx errors) and graceful handling of Rate Limiting (429).
- [x] **Enhanced Error Handling**: Introduce granular exception classes for `OverQueryLimitException` and `RequestDeniedException`.

### Performance & DX
- [x] **Response Caching**: Implement drivers (Redis/File) to cache Distance Matrix responses, reducing API costs.
- [x] **Request Middleware**: Allow injection of Guzzle middleware for logging and debugging.
- [x] **Data Objects**: Refactor internal array manipulations to use strict DTOs for better maintenance.
- [x] **Helper Traits**: Add `Routable` traits for Eloquent models (e.g., `$user->address()->to($store)`).

## v1.5 - Async Support

Focus: High-concurrency applications.

- [ ] **Asynchronous Requests**: Add support for async requests using Guzzle Promises, allowing multiple route calculations to be dispatched in parallel.
- [ ] **Batch Processing**: Utilities for handling large Distance Matrix requests by automatically chunking them to stay within API limits.

## v2.0 - Ecosystem Expansion

Focus: Broader support and integrations.

- [ ] **Provider Abstraction**: Refactor the core to support multiple routing providers (e.g., Mapbox, Bing Maps, OpenRouteService) via a driver-based approach.
- [ ] **Nova Integration**: A Laravel Nova tool/card to visualize routes or matrix data directly in the dashboard.
- [ ] **Filament Plugin**: dedicated Filament form components and widgets.

## Long Term Goals

- **UI Components**: A set of headless UI components (Vue/React via Inertia) for displaying maps and route details.
- **CLI Tools**: Artisan commands to test routes or generate matrix reports from the command line.
