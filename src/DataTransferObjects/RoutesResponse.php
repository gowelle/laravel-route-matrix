<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\DataTransferObjects;

use Illuminate\Support\Collection;

/**
 * The response from a computeRoutes API call.
 */
final readonly class RoutesResponse
{
    /**
     * @param  Collection<int, Route>  $routes
     */
    public function __construct(
        public Collection $routes,
        public ?FallbackInfo $fallbackInfo = null,
        public ?GeocodingResults $geocodingResults = null,
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        $routes = collect($data['routes'] ?? [])
            ->map(fn (array $route) => Route::fromArray($route));

        return new self(
            routes: $routes,
            fallbackInfo: isset($data['fallbackInfo']) ? FallbackInfo::fromArray($data['fallbackInfo']) : null,
            geocodingResults: isset($data['geocodingResults']) ? GeocodingResults::fromArray($data['geocodingResults']) : null,
        );
    }

    /**
     * Check if routes were found.
     */
    public function hasRoutes(): bool
    {
        return $this->routes->isNotEmpty();
    }

    /**
     * Get the first (recommended) route.
     */
    public function first(): ?Route
    {
        return $this->routes->first();
    }

    /**
     * Get the default route (first route).
     */
    public function getDefaultRoute(): ?Route
    {
        return $this->first();
    }

    /**
     * Get all alternative routes (excluding the first/default route).
     *
     * @return Collection<int, Route>
     */
    public function getAlternatives(): Collection
    {
        return $this->routes->skip(1)->values();
    }

    /**
     * Get the fuel-efficient route if available.
     */
    public function getFuelEfficientRoute(): ?Route
    {
        return $this->routes->first(fn (Route $route) => $route->isFuelEfficient());
    }
}
