<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Facades;

use Gowelle\LaravelRouteMatrix\Contracts\GoogleRoutesClientInterface;
use Gowelle\LaravelRouteMatrix\DataTransferObjects\RouteMatrixResponse;
use Gowelle\LaravelRouteMatrix\DataTransferObjects\RoutesResponse;
use Gowelle\LaravelRouteMatrix\RouteMatrixRequest;
use Gowelle\LaravelRouteMatrix\RouteRequest;
use Illuminate\Support\Facades\Facade;

/**
 * @method static RouteRequest from(array|string $origin)
 * @method static RouteMatrixRequest matrix()
 * @method static RoutesResponse computeRoutes(RouteRequest $request)
 * @method static RouteMatrixResponse computeRouteMatrix(RouteMatrixRequest $request)
 * @method static string|null getApiKey()
 *
 * @see \Gowelle\LaravelRouteMatrix\GoogleRoutesClient
 */
class GoogleRoutes extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return GoogleRoutesClientInterface::class;
    }
}
