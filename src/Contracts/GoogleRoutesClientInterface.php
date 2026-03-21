<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Contracts;

use Gowelle\LaravelRouteMatrix\DataTransferObjects\RouteMatrixResponse;
use Gowelle\LaravelRouteMatrix\DataTransferObjects\RoutesResponse;
use Gowelle\LaravelRouteMatrix\Exceptions\GoogleRoutesException;
use Gowelle\LaravelRouteMatrix\RouteMatrixRequest;
use Gowelle\LaravelRouteMatrix\RouteRequest;

/**
 * Contract for Google Routes API client implementations.
 */
interface GoogleRoutesClientInterface
{
    /**
     * Compute routes based on the given request.
     *
     * @throws GoogleRoutesException
     */
    public function computeRoutes(RouteRequest $request): RoutesResponse;

    /**
     * Compute a route matrix based on the given request.
     *
     * @throws GoogleRoutesException
     */
    public function computeRouteMatrix(RouteMatrixRequest $request): RouteMatrixResponse;

    /**
     * Start building a new route request from an origin.
     *
     * @param  Routable|array{lat?: float, latitude?: float, lng?: float, longitude?: float}|string  $origin
     */
    public function from(Routable|array|string $origin): RouteRequest;

    /**
     * Start building a new route matrix request.
     */
    public function matrix(): RouteMatrixRequest;

    /**
     * Get the API key being used.
     */
    public function getApiKey(): ?string;
}
