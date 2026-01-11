<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Contracts;

use Gowelle\LaravelRouteMatrix\DataTransferObjects\RoutesResponse;
use Gowelle\LaravelRouteMatrix\RouteRequest;

/**
 * Contract for Google Routes API client implementations.
 */
interface GoogleRoutesClientInterface
{
    /**
     * Compute routes based on the given request.
     *
     * @throws \Gowelle\LaravelRouteMatrix\Exceptions\GoogleRoutesException
     */
    public function computeRoutes(RouteRequest $request): RoutesResponse;

    /**
     * Start building a new route request from an origin.
     *
     * @param  array{lat?: float, latitude?: float, lng?: float, longitude?: float}|string  $origin
     */
    public function from(array|string $origin): RouteRequest;

    /**
     * Get the API key being used.
     */
    public function getApiKey(): ?string;
}
